<?php

namespace App\Modules\WorkOs\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Modules\WorkOs\Services\AuthPlatform\OAuthEngine;
use App\Modules\WorkOs\Services\AuthPlatform\SessionEngine;
use Illuminate\Support\Facades\Auth;
use Exception;

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
            return redirect()->route('login')->with('error', 'OAuth initialization failed: ' . $e->getMessage());
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
                return redirect()->route('auth.oauth.register.view')->with('info', 'Silakan lengkapi pendaftaran untuk menghubungkan akun ' . ucfirst($provider) . ' Anda.');
            }

            $user = $result;

            // 2. Authenticate the user (creates Laravel session)
            Auth::login($user, remember: false);

            // 3. Prevent session fixation — regenerate session ID post-login
            $request->session()->regenerate();

            // 4. Create enterprise session record with device fingerprint + risk score
            $session = $this->sessionEngine->createSession($user, $request);

            // 5. Store auth session token in the Laravel session for quick lookup
            $request->session()->put('auth_session_token', $session->session_token);

            // 6. Redirect to dashboard
            return redirect()->intended(route('dashboard', absolute: false))
                ->with('success', 'Successfully signed in with ' . ucfirst($provider) . '.');

        } catch (Exception $e) {
            return redirect()->route('login')
                ->with('error', 'OAuth authentication failed: ' . $e->getMessage());
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
        if (!session()->has('oauth_register_data')) {
            return redirect()->route('login')->with('error', 'Sesi pendaftaran OAuth kedaluwarsa atau tidak valid.');
        }

        return \Inertia\Inertia::render('auth/OAuthRegister', [
            'oauthData' => session()->get('oauth_register_data'),
        ]);
    }

    /**
     * Process registration for new OAuth users.
     */
    public function registerStore(Request $request)
    {
        if (!session()->has('oauth_register_data')) {
            return redirect()->route('login')->with('error', 'Sesi pendaftaran OAuth kedaluwarsa atau tidak valid.');
        }

        $oauthData = session()->get('oauth_register_data');

        // Validation
        $allowedRoles = ['mahasiswa', 'alumni', 'mitra'];
        $rules = [
            'role'        => ['required', 'string', \Illuminate\Validation\Rule::in($allowedRoles)],
            'nomor_induk' => ['required', 'string', 'max:50', \Illuminate\Validation\Rule::unique('users', 'nomor_induk')],
        ];

        if ($request->role === 'mahasiswa' || $request->role === 'alumni') {
            $rules['program_studi_id'] = ['required', 'integer', \Illuminate\Validation\Rule::exists('program_studis', 'id')];
        }

        if ($request->role === 'alumni') {
            $rules['tahun_lulus'] = ['required', 'digits:4', 'integer', 'min:1990', 'max:' . date('Y')];
        }

        if ($request->role === 'mitra') {
            $rules['no_telepon'] = ['required', 'string', 'max:20'];
            $rules['nama_perusahaan'] = ['required', 'string', 'max:100'];
        }

        $request->validate($rules, [
            'nomor_induk.unique'  => 'Akun dengan NIM/NIB ini telah terdaftar, silakan login.',
            'program_studi_id.required' => 'Program Studi wajib dipilih.',
            'tahun_lulus.required'     => 'Tahun lulus wajib diisi.',
            'no_telepon.required'       => 'Nomor telepon wajib diisi.',
            'nama_perusahaan.required'  => 'Nama perusahaan wajib diisi.',
        ]);

        // Double check if user with this email was created in the meantime
        $existingUser = \App\Models\User::where('email', $oauthData['email'])->first();
        if ($existingUser) {
            // Security: Jika akun lokal belum terverifikasi emailnya, jangan lakukan implicit linking untuk mencegah pembajakan akun (Account Takeover)
            if (!$existingUser->email_verified_at) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'email' => 'Akun dengan email ini telah terdaftar tetapi belum terverifikasi. Silakan verifikasi email Anda terlebih dahulu.'
                ]);
            }

            // Update user_type if not set
            if (!$existingUser->user_type && $request->role) {
                $existingUser->update(['user_type' => $request->role]);
            }

            // Link OAuth Credential safely
            try {
                \App\Models\AuthOAuthCredential::updateOrCreate(
                    [
                        'provider_id' => $oauthData['provider_id'],
                        'external_id' => $oauthData['external_id'],
                    ],
                    [
                        'user_id'      => $existingUser->id,
                        'email'        => $oauthData['email'],
                        'access_token' => $oauthData['access_token'],
                        'refresh_token'=> $oauthData['refresh_token'] ?? null,
                        'expires_at'   => $oauthData['expires_at'] ?? null,
                    ]
                );
            } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                $credential = \App\Models\AuthOAuthCredential::where('provider_id', $oauthData['provider_id'])
                    ->where('external_id', $oauthData['external_id'])
                    ->first();
                if ($credential) {
                    $credential->update([
                        'user_id'      => $existingUser->id,
                        'email'        => $oauthData['email'],
                        'access_token' => $oauthData['access_token'],
                        'refresh_token'=> $oauthData['refresh_token'] ?? null,
                        'expires_at'   => $oauthData['expires_at'] ?? null,
                    ]);
                } else {
                    throw $e;
                }
            }

            // Ensure they have default module roles if they don't have any
            if (!\App\Models\UserModuleRole::where('user_id', $existingUser->id)->exists()) {
                $roleType = $existingUser->user_type ?: $request->role;
                $roleObj = \App\Models\Role::where('slug', $roleType)->first();
                if ($roleObj) {
                    $defaultModules = [];
                    if ($roleType === 'mahasiswa') {
                        $defaultModules = ['FAST', 'PAGI', 'WIMS'];
                    } elseif ($roleType === 'alumni') {
                        $defaultModules = ['TRACE', 'PAGI'];
                    } elseif ($roleType === 'mitra') {
                        $defaultModules = ['WIMS', 'TRACE'];
                    }

                    if (!empty($defaultModules)) {
                        $modules = \App\Models\Module::whereIn('code', $defaultModules)->get();
                        foreach ($modules as $mod) {
                            \App\Models\UserModuleRole::create([
                                'user_id'   => $existingUser->id,
                                'module_id' => $mod->id,
                                'role_id'   => $roleObj->id,
                                'is_active' => true,
                            ]);
                        }
                    }
                }
            }

            session()->forget('oauth_register_data');
            Auth::login($existingUser, remember: false);
            $request->session()->regenerate();
            $session = $this->sessionEngine->createSession($existingUser, $request);
            $request->session()->put('auth_session_token', $session->session_token);

            return redirect()->intended(route('dashboard', absolute: false))
                ->with('success', 'Akun Anda sudah terdaftar dan berhasil dihubungkan.');
        }

        // Create User using server-side session data for name and email
        $user = \App\Models\User::create([
            'name'                => $oauthData['name'],
            'email'               => $oauthData['email'],
            'password'            => \Illuminate\Support\Str::random(32),
            'user_type'           => $request->role,
            'nomor_induk'         => $request->nomor_induk,
            'status_approval'     => 'approved',
            'password_changed_at' => now(),
            'program_studi_id'    => $request->program_studi_id ?? null,
            'tahun_lulus'         => $request->tahun_lulus ?? null,
            'no_telepon'          => $request->no_telepon ?? null,
        ]);

        $user->email_verified_at = now();
        $user->save();

        // Link OAuth Credential safely
        try {
            \App\Models\AuthOAuthCredential::updateOrCreate(
                [
                    'provider_id' => $oauthData['provider_id'],
                    'external_id' => $oauthData['external_id'],
                ],
                [
                    'user_id'      => $user->id,
                    'email'        => $oauthData['email'],
                    'access_token' => $oauthData['access_token'],
                    'refresh_token'=> $oauthData['refresh_token'] ?? null,
                    'expires_at'   => $oauthData['expires_at'] ?? null,
                ]
            );
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            $credential = \App\Models\AuthOAuthCredential::where('provider_id', $oauthData['provider_id'])
                ->where('external_id', $oauthData['external_id'])
                ->first();
            if ($credential) {
                $credential->update([
                    'user_id'      => $user->id,
                    'email'        => $oauthData['email'],
                    'access_token' => $oauthData['access_token'],
                    'refresh_token'=> $oauthData['refresh_token'] ?? null,
                    'expires_at'   => $oauthData['expires_at'] ?? null,
                ]);
            } else {
                throw $e;
            }
        }

        // Clear session data
        session()->forget('oauth_register_data');

        // Auto-assign default module access
        $roleObj = \App\Models\Role::where('slug', $user->user_type)->first();
        if ($roleObj) {
            $defaultModules = [];
            if ($user->user_type === 'mahasiswa') {
                $defaultModules = ['FAST', 'PAGI', 'WIMS'];
            } elseif ($user->user_type === 'alumni') {
                $defaultModules = ['TRACE', 'PAGI'];
            } elseif ($user->user_type === 'mitra') {
                $defaultModules = ['WIMS', 'TRACE'];
            }

            if (!empty($defaultModules)) {
                $modules = \App\Models\Module::whereIn('code', $defaultModules)->get();
                foreach ($modules as $mod) {
                    \App\Models\UserModuleRole::create([
                        'user_id'   => $user->id,
                        'module_id' => $mod->id,
                        'role_id'   => $roleObj->id,
                        'is_active' => true,
                    ]);
                }
            }
        }

        // Log the user in
        Auth::login($user, remember: false);

        // Regenerate session
        $request->session()->regenerate();

        // Create enterprise session record
        $session = $this->sessionEngine->createSession($user, $request);
        $request->session()->put('auth_session_token', $session->session_token);

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('success', 'Pendaftaran berhasil. Akun Google Anda telah terhubung.');
    }
}
