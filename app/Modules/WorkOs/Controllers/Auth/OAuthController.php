<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Enums\RegistrationStatus;
use App\Enums\UserAccountStatus;
use App\Http\Controllers\Controller;
use App\Models\Auth\AuthOAuthCredential;
use App\Models\Auth\RegistrationRequest;
use App\Models\Module;
use App\Models\User;
use App\Models\UserModuleRole;
use App\Modules\WorkOs\Services\AuthPlatform\OAuthEngine;
use App\Modules\WorkOs\Services\AuthPlatform\SessionEngine;
use Exception;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

/**
 * OAuthController — PUBLIC endpoints only.
 * Must NEVER be behind auth middleware.
 * The OAuth callback arrives before the user has any session.
 */
class OAuthController extends Controller
{
    public function __construct(
        protected OAuthEngine $oauthEngine,
        protected SessionEngine $sessionEngine,
    ) {}

    /**
     * Generate and return the authorization URL for a given provider.
     * Called from the frontend via POST /auth/oauth/{provider}/redirect
     */
    public function redirect(string $provider)
    {
        try {
            $url = $this->oauthEngine->getAuthorizationUrl($provider);

            return redirect()->away($url);
        } catch (Exception $e) {
            logger()->error('OAuth redirect failed: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->route('login')->with('error', 'Gagal memulai autentikasi OAuth. Silakan coba lagi.');
        }
    }

    /**
     * Handle the OAuth callback from the identity provider.
     * Must be PUBLIC — the IdP sends the user here after authentication.
     *
     * Security measures:
     *  - State validation handled by Socialite (CSRF equivalent)
     *  - Throttle:oauth rate limiter applied in route definition
     *  - Session fixation prevention via session()->regenerate() after login
     */
    public function callback(string $provider, Request $request)
    {
        try {
            // 1. Exchange code → token → user identity via OAuthEngine
            $result = $this->oauthEngine->handleCallback($provider, $request->all());

            // Check if user needs to register first
            if (is_array($result) && isset($result['needs_registration'])) {
                session()->put('oauth_register_data', $result['oauth_data']);

                return redirect()->route('auth.oauth.register.view')->with('info', 'Silakan lengkapi pendaftaran untuk menghubungkan akun '.ucfirst($provider).' Anda.');
            }

            $user = $result;

            // ── Account Lifecycle Check ──────────────────────────────────────
            if (! $user->isAccountActive()) {
                $msg = $user->getLoginBlockMessage() ?? 'Akun Anda tidak dapat diakses saat ini.';

                return redirect()->route('login')->with('error', $msg);
            }

            // 2. Prevent session fixation BEFORE Auth::login() fires the Login event.
            $request->session()->regenerate();

            // 3. Authenticate the user (fires Login event, writes user to session)
            Auth::login($user, remember: false);

            // 4. Create enterprise session record with device fingerprint + risk score
            $session = $this->sessionEngine->createSession($user, $request);

            // 5. Store auth session token (model UUID) in the Laravel session for quick lookup
            $request->session()->put('auth_session_token', $session->id);

            // 6. Redirect to dashboard
            return redirect()->intended(route('dashboard', absolute: false))
                ->with('success', 'Successfully signed in with '.ucfirst($provider).'.');

        } catch (Exception $e) {
            logger()->error('OAuth callback failed: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->route('login')
                ->with('error', 'Autentikasi OAuth gagal atau sesi telah kedaluwarsa. Silakan coba login kembali.');
        }
    }

    /**
     * Disconnect a linked OAuth provider from the user's account.
     * Requires authentication.
     *
     * [FIX C-5] Sebelumnya: tidak ada guard untuk mencegah user memutus satu-satunya
     * metode login mereka. Jika user tidak punya password lokal (random hash) dan
     * hanya punya 1 OAuth provider, disconnect akan membuat akun terkunci permanen.
     *
     * Sekarang: cek apakah user punya cara login lain:
     *   1. Password lokal yang diset sendiri (password_changed_at tidak null)
     *   2. OAuth provider lain yang masih terhubung
     * Jika tidak ada keduanya → tolak disconnect dengan pesan yang jelas.
     */
    public function disconnect(string $provider, Request $request)
    {
        $user = $request->user();

        // [FIX C-5] Cek apakah user punya password lokal yang sudah diset sendiri
        $hasLocalPassword = ! is_null($user->password_changed_at);

        // Hitung jumlah OAuth provider lain yang masih terhubung (selain yang akan di-disconnect)
        $otherOAuthCount = $user->oauthCredentials()
            ->whereHas('provider', function ($q) use ($provider) {
                $q->where('slug', '!=', $provider);
            })
            ->count();

        // Jika tidak punya password lokal DAN tidak punya OAuth lain → tolak
        if (! $hasLocalPassword && $otherOAuthCount === 0) {
            return response()->json([
                'error' => 'Tidak dapat memutus koneksi '.ucfirst($provider).'. '
                    .'Ini adalah satu-satunya metode login Anda. '
                    .'Silakan buat password lokal terlebih dahulu melalui halaman Pengaturan Keamanan.',
            ], 422);
        }

        try {
            $this->oauthEngine->disconnect($user, $provider);

            return response()->json(['message' => 'Provider disconnected successfully.']);
        } catch (Exception $e) {
            Log::warning('[OAuthController] Disconnect failed', [
                'user_id' => $user->id,
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Show dedicated OAuth registration page.
     */
    public function registerView(Request $request)
    {
        if (! session()->has('oauth_register_data')) {
            return redirect()->route('login')->with('error', 'Sesi pendaftaran OAuth kedaluwarsa atau tidak valid.');
        }

        return Inertia::render('auth/OAuthRegister', [
            'oauthData' => session()->get('oauth_register_data'),
        ]);
    }

    /**
     * Process registration for new OAuth users.
     */
    public function registerStore(Request $request)
    {
        if (! session()->has('oauth_register_data')) {
            return redirect()->route('login')->with('error', 'Sesi pendaftaran OAuth kedaluwarsa atau tidak valid.');
        }

        $oauthData = session()->get('oauth_register_data');

        // [FIX FIND-003] Bungkus seluruh registrasi OAuth dalam satu DB transaction.
        // Sebelumnya ada dua User::where() check yang terpisah memungkinkan race condition
        // di mana dua request bersamaan keduanya lolos pengecekan pertama.
        // DB::transaction + firstOrCreate dengan unique constraint DB mencegah duplikasi.

        // If the user already exists in the DB with the same email, we link them instead of creating a new user,
        // so we shouldn't fail validation on nomor_induk unique check.
        $existingUser = User::where('email', '=', $oauthData['email'], 'and')->first();

        // Validation
        $allowedRoles = ['mahasiswa', 'alumni', 'mitra'];
        $nomorIndukRules = ['required', 'string', 'max:50'];
        if (! $existingUser) {
            $nomorIndukRules[] = Rule::unique('users', 'nomor_induk');
        }
        $rules = [
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'nomor_induk' => $nomorIndukRules,
        ];

        if ($request->role === 'mahasiswa' || $request->role === 'alumni') {
            $rules['program_studi_id'] = ['required', 'integer', Rule::exists('program_studis', 'id')];
        }

        if ($request->role === 'alumni') {
            $rules['tahun_lulus'] = ['required', 'digits:4', 'integer', 'min:1990', 'max:'.date('Y')];
        }

        if ($request->role === 'mitra') {
            $rules['no_telepon'] = ['required', 'string', 'max:20'];
            $rules['nama_perusahaan'] = ['required', 'string', 'max:100'];
        }

        $request->validate($rules, [
            'nomor_induk.unique' => 'Akun dengan NIM/NIB ini telah terdaftar, silakan login.',
            'program_studi_id.required' => 'Program Studi wajib dipilih.',
            'tahun_lulus.required' => 'Tahun lulus wajib diisi.',
            'no_telepon.required' => 'Nomor telepon wajib diisi.',
            'nama_perusahaan.required' => 'Nama perusahaan wajib diisi.',
        ]);

        // Double check if user with this email was created in the meantime
        $existingUser = User::where('email', '=', $oauthData['email'], 'and')->first();
        if ($existingUser) {
            return DB::transaction(function () use ($request, $oauthData, $existingUser) {
                // Re-fetch with lock inside transaction
                $existingUser = User::where('email', '=', $oauthData['email'], 'and')->lockForUpdate()->first();

                if (! $existingUser) {
                    // User was deleted between the two checks — proceed to creation below
                    return null;
                }

                // Security: Jika akun lokal belum terverifikasi emailnya, jangan lakukan implicit linking
                // untuk mencegah pembajakan akun (Account Takeover)
                if (! $existingUser->email_verified_at) {
                    throw ValidationException::withMessages([
                        'email' => 'Akun dengan email ini telah terdaftar tetapi belum diverifikasi. Silakan verifikasi email Anda terlebih dahulu.',
                    ]);
                }

                // Update user_type if not set
                if (! $existingUser->user_type && $request->role) {
                    $existingUser->fill(['user_type' => $request->role])->save();
                }

                // Link OAuth Credential safely
                try {
                    AuthOAuthCredential::updateOrCreate(
                        [
                            'provider_id' => $oauthData['provider_id'],
                            'external_id' => $oauthData['external_id'],
                        ],
                        [
                            'user_id' => $existingUser->id,
                            'email' => $oauthData['email'],
                            'access_token' => $oauthData['access_token'],
                            'refresh_token' => $oauthData['refresh_token'] ?? null,
                            'expires_at' => $oauthData['expires_at'] ?? null,
                        ]
                    );
                } catch (UniqueConstraintViolationException $e) {
                    $credential = AuthOAuthCredential::where('provider_id', '=', $oauthData['provider_id'], 'and')
                        ->where('external_id', '=', $oauthData['external_id'], 'and')
                        ->first();
                    if ($credential) {
                        $credential->fill([
                            'user_id' => $existingUser->id,
                            'email' => $oauthData['email'],
                            'access_token' => $oauthData['access_token'],
                            'refresh_token' => $oauthData['refresh_token'] ?? null,
                            'expires_at' => $oauthData['expires_at'] ?? null,
                        ])->save();
                    } else {
                        throw $e;
                    }
                }

                // Ensure they have default module roles if they don't have any
                if (! UserModuleRole::where('user_id', '=', $existingUser->id, 'and')->exists()) {
                    if (! $existingUser->user_type && $request->role) {
                        $existingUser->user_type = $request->role;
                        $existingUser->save();
                    }
                    $existingUser->assignDefaultModuleRoles();
                }

                // Status check for existing user
                if (! $existingUser->isAccountActive()) {
                    $msg = $existingUser->getLoginBlockMessage() ?? 'Akun Anda tidak dapat diakses.';
                    throw ValidationException::withMessages(['email' => $msg]);
                }

                session()->forget('oauth_register_data');
                $request->session()->regenerate(); // Prevent session fixation before login event fires
                Auth::login($existingUser, remember: false);
                $session = $this->sessionEngine->createSession($existingUser, $request);
                $request->session()->put('auth_session_token', $session->id);

                return redirect()->intended(route('dashboard', absolute: false))
                    ->with('success', 'Akun Anda sudah terdaftar dan berhasil dihubungkan.');
            }) ?? $this->createNewOAuthUser($request, $oauthData);
        }

        return $this->createNewOAuthUser($request, $oauthData);
    }

    /**
     * [FIX FIND-003] Buat user baru untuk OAuth registration dalam satu transaction.
     */
    private function createNewOAuthUser(Request $request, array $oauthData): mixed
    {
        return DB::transaction(function () use ($request, $oauthData) {
            // [FIX FIND-003] Re-check inside transaction dengan lock untuk cegah race condition
            $existingUser = User::where('email', '=', $oauthData['email'], 'and')->lockForUpdate()->first();
            if ($existingUser) {
                // Concurrent request sudah membuat user ini — delegate ke alur existing user
                session()->forget('oauth_register_data');

                return redirect()->route('login')->with(
                    'status',
                    'Akun dengan email ini sudah ada. Silakan login.'
                );
            }

            // [FIX HIGH-04] OAuth tokens are encrypted before storage using APP_KEY.
            $registrationRequest = RegistrationRequest::create([
                'full_name' => $oauthData['name'],
                'email' => $oauthData['email'],
                'role' => $request->role ?? 'alumni',
                'status' => RegistrationStatus::Pending->value,
                'oauth_data' => [
                    'provider' => $oauthData['provider'],
                    'provider_id' => $oauthData['provider_id'],
                    'external_id' => $oauthData['external_id'],
                    'name' => $oauthData['name'],
                    'email' => $oauthData['email'],
                    'access_token' => $this->encryptToken($oauthData['access_token'] ?? null),
                    'refresh_token' => $this->encryptToken($oauthData['refresh_token'] ?? null),
                    'expires_at' => $oauthData['expires_at'] ?? null,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Create a temporary pending User record
            $tempUser = new User([
                'name' => $oauthData['name'],
                'email' => $oauthData['email'],
                'password' => Hash::make(Str::random(32)),
            ]);
            $tempUser->user_type = $request->role;
            $tempUser->status_approval = UserAccountStatus::Pending;
            $tempUser->email_verified_at = now();
            $tempUser->is_active = false;
            $tempUser->save();

            $registrationRequest->update(['created_user_id' => $tempUser->id]);

            session()->forget('oauth_register_data');

            return redirect()->route('login')->with(
                'status',
                'Pendaftaran Anda sedang diproses oleh admin. '.
                'Anda akan mendapatkan email setelah akun disetujui.'
            );
        });
    }

    /**
     * [FIX HIGH-04] Encrypt an OAuth token before storing in the database.
     * Uses Laravel's Crypt facade backed by APP_KEY.
     * Returns null if the token is empty/null to avoid storing empty encrypted strings.
     */
    /**
     * [FIX HIGH-04] Encrypt an OAuth token before storing in the database.
     * Uses Laravel's Crypt facade backed by APP_KEY.
     * Returns null if the token is empty/null to avoid storing empty encrypted strings.
     */
    private function encryptToken(?string $token): ?string
    {
        if (empty($token)) {
            return null;
        }

        return Crypt::encryptString($token);
    }

    /**
     * Display the Smart Access Control Verification page for approved OAuth users.
     */
    public function verifyAccessView(Request $request)
    {
        $requestId = $request->query('request_id');
        $token = $request->query('token');

        if (! $requestId) {
            return Inertia::render('auth/SmartAccessVerification', [
                'isValid' => false,
                'errorMessage' => 'Tautan verifikasi tidak valid (parameter request_id tidak ditemukan).',
            ]);
        }

        $regRequest = RegistrationRequest::find($requestId);
        if (! $regRequest) {
            return Inertia::render('auth/SmartAccessVerification', [
                'isValid' => false,
                'errorMessage' => 'Data pendaftaran tidak ditemukan dalam sistem.',
            ]);
        }

        $user = $regRequest->createdUser ?? User::where('email', '=', $regRequest->email, 'and')->first();
        if (! $user) {
            return Inertia::render('auth/SmartAccessVerification', [
                'isValid' => false,
                'errorMessage' => 'Data akun pengguna tidak ditemukan dalam sistem.',
            ]);
        }

        // If user is already active, allow direct access
        if ($user->is_active && $user->status_approval === UserAccountStatus::Activated) {
            return Inertia::render('auth/SmartAccessVerification', [
                'isValid' => true,
                'isAlreadyActive' => true,
                'userData' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->user_type ?? $regRequest->role,
                    'provider' => $regRequest->oauth_data['provider'] ?? 'Google',
                ],
                'signedParams' => [
                    'request_id' => $requestId,
                    'token' => $token ?? '',
                    'signature' => $request->query('signature'),
                    'expires' => $request->query('expires'),
                ],
            ]);
        }

        // Check signed URL signature or token validity
        $hasValidSignature = $request->hasValidSignature();
        $hasValidToken = $token ? $regRequest->verifyActivationToken((string) $token) : true;

        if (! $hasValidSignature && ! ($token && $hasValidToken)) {
            return Inertia::render('auth/SmartAccessVerification', [
                'isValid' => false,
                'errorMessage' => 'Tautan verifikasi tidak valid atau telah kedaluwarsa. Silakan minta tautan baru dari administrator.',
            ]);
        }

        return Inertia::render('auth/SmartAccessVerification', [
            'isValid' => true,
            'isAlreadyActive' => false,
            'userData' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->user_type ?? $regRequest->role,
                'provider' => $regRequest->oauth_data['provider'] ?? 'Google',
            ],
            'signedParams' => [
                'request_id' => $requestId,
                'token' => $token ?? '',
                'signature' => $request->query('signature'),
                'expires' => $request->query('expires'),
            ],
        ]);
    }

    /**
     * Submit and activate OAuth user session after Smart Access animation completes.
     */
    public function verifyAccessSubmit(Request $request)
    {
        $requestId = $request->input('request_id');
        $token = $request->input('token');

        $regRequest = RegistrationRequest::find($requestId);
        if (! $regRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Data pendaftaran tidak ditemukan.',
            ], 404);
        }

        $user = $regRequest->createdUser ?? User::where('email', '=', $regRequest->email, 'and')->first();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan dalam sistem.',
            ], 404);
        }

        // If user is not yet active, perform signature/token check and activate
        if (! $user->is_active) {
            $hasValidSignature = $request->hasValidSignature();
            $hasValidToken = $token ? $regRequest->verifyActivationToken((string) $token) : true;

            if (! $hasValidSignature && ! ($token && $hasValidToken)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tautan verifikasi telah kedaluwarsa atau tanda tangan tidak valid.',
                ], 403);
            }

            // Activate user
            $user->forceFill([
                'is_active' => true,
                'status_approval' => UserAccountStatus::Activated->value,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();

            $regRequest->fill(['status' => RegistrationStatus::Activated->value])->save();
        }

        // Ensure default module roles
        if (! UserModuleRole::where('user_id', '=', $user->id, 'and')->exists()) {
            $user->assignDefaultModuleRoles();
        }

        // Log in user and establish session
        $request->session()->regenerate();
        Auth::login($user, remember: false);
        $session = $this->sessionEngine->createSession($user, $request);
        $request->session()->put('auth_session_token', $session->id);

        return response()->json([
            'success' => true,
            'redirect_url' => route('dashboard', absolute: false),
            'message' => 'Verifikasi berhasil! Mengalihkan ke dashboard...',
        ]);
    }
}
