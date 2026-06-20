<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Auth\AuthOAuthCredential;
use App\Models\Module;
use App\Models\User;
use App\Models\UserModuleRole;
use App\Modules\WorkOs\Services\AuthPlatform\OAuthEngine;
use App\Modules\WorkOs\Services\AuthPlatform\SessionEngine;
use Exception;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

            // Check if user is active and approved
            if (! $user->is_active) {
                return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan.');
            }
            if ($user->status_approval !== 'approved') {
                return redirect()->route('login')->with('error', 'Akun Anda belum disetujui atau telah ditolak.');
            }

            // 2. Authenticate the user (creates Laravel session)
            Auth::login($user, remember: false);

            // 3. Prevent session fixation — regenerate session ID post-login
            $request->session()->regenerate();

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
     */
    public function disconnect(string $provider, Request $request)
    {
        try {
            $this->oauthEngine->disconnect($request->user(), $provider);

            return response()->json(['message' => 'Provider disconnected successfully.']);
        } catch (Exception $e) {
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

        // Validation
        $allowedRoles = ['mahasiswa', 'alumni', 'mitra'];
        $rules = [
            'role' => ['required', 'string', Rule::in($allowedRoles)],
            'nomor_induk' => ['required', 'string', 'max:50', Rule::unique('users', 'nomor_induk')],
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
        $existingUser = User::where('email', $oauthData['email'])->first();
        if ($existingUser) {
            // Security: Jika akun lokal belum terverifikasi emailnya, jangan lakukan implicit linking untuk mencegah pembajakan akun (Account Takeover)
            if (! $existingUser->email_verified_at) {
                throw ValidationException::withMessages([
                    'email' => 'Akun dengan email ini telah terdaftar tetapi belum terverifikasi. Silakan verifikasi email Anda terlebih dahulu.',
                ]);
            }

            // Update user_type if not set
            if (! $existingUser->user_type && $request->role) {
                $existingUser->update(['user_type' => $request->role]);
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
                $credential = AuthOAuthCredential::where('provider_id', $oauthData['provider_id'])
                    ->where('external_id', $oauthData['external_id'])
                    ->first();
                if ($credential) {
                    $credential->update([
                        'user_id' => $existingUser->id,
                        'email' => $oauthData['email'],
                        'access_token' => $oauthData['access_token'],
                        'refresh_token' => $oauthData['refresh_token'] ?? null,
                        'expires_at' => $oauthData['expires_at'] ?? null,
                    ]);
                } else {
                    throw $e;
                }
            }

            // Ensure they have default module roles if they don't have any
            if (! UserModuleRole::where('user_id', $existingUser->id)->exists()) {
                if (! $existingUser->user_type && $request->role) {
                    $existingUser->user_type = $request->role;
                    $existingUser->save();
                }
                $existingUser->assignDefaultModuleRoles();
            }

            // Check if user is active and approved
            if (! $existingUser->is_active) {
                throw ValidationException::withMessages([
                    'email' => 'Akun Anda telah dinonaktifkan.',
                ]);
            }
            if ($existingUser->status_approval !== 'approved') {
                throw ValidationException::withMessages([
                    'email' => 'Akun Anda belum disetujui atau telah ditolak.',
                ]);
            }

            session()->forget('oauth_register_data');
            Auth::login($existingUser, remember: false);
            $request->session()->regenerate();
            $session = $this->sessionEngine->createSession($existingUser, $request);
            $request->session()->put('auth_session_token', $session->id);

            return redirect()->intended(route('dashboard', absolute: false))
                ->with('success', 'Akun Anda sudah terdaftar dan berhasil dihubungkan.');
        }

        // Create User using server-side session data for name and email
        $user = User::create([
            'name' => $oauthData['name'],
            'email' => $oauthData['email'],
            'password' => Str::random(32),
            'user_type' => $request->role,
            'nomor_induk' => $request->nomor_induk,
            'status_approval' => 'approved',
            'password_changed_at' => now(),
            'program_studi_id' => $request->program_studi_id ?? null,
            'tahun_lulus' => $request->tahun_lulus ?? null,
            'no_telepon' => $request->no_telepon ?? null,
        ]);

        $user->email_verified_at = now();
        $user->save();

        // Link OAuth Credential safely
        try {
            AuthOAuthCredential::updateOrCreate(
                [
                    'provider_id' => $oauthData['provider_id'],
                    'external_id' => $oauthData['external_id'],
                ],
                [
                    'user_id' => $user->id,
                    'email' => $oauthData['email'],
                    'access_token' => $oauthData['access_token'],
                    'refresh_token' => $oauthData['refresh_token'] ?? null,
                    'expires_at' => $oauthData['expires_at'] ?? null,
                ]
            );
        } catch (UniqueConstraintViolationException $e) {
            $credential = AuthOAuthCredential::where('provider_id', $oauthData['provider_id'])
                ->where('external_id', $oauthData['external_id'])
                ->first();
            if ($credential) {
                $credential->update([
                    'user_id' => $user->id,
                    'email' => $oauthData['email'],
                    'access_token' => $oauthData['access_token'],
                    'refresh_token' => $oauthData['refresh_token'] ?? null,
                    'expires_at' => $oauthData['expires_at'] ?? null,
                ]);
            } else {
                throw $e;
            }
        }

        // Clear session data
        session()->forget('oauth_register_data');

        // Auto-assign default module access
        $user->assignDefaultModuleRoles();

        // Log the user in
        Auth::login($user, remember: false);

        // Regenerate session
        $request->session()->regenerate();

        // Create enterprise session record
        $session = $this->sessionEngine->createSession($user, $request);
        $request->session()->put('auth_session_token', $session->id);

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('success', 'Pendaftaran berhasil. Akun Google Anda telah terhubung.');
    }
}
