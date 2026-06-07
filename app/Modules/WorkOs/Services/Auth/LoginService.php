<?php

namespace App\Modules\WorkOs\Services\Auth;

use App\Models\User;
use App\Models\AuthLoginAttempt;
use App\Modules\WorkOs\Services\AuthPlatform\SessionEngine;
use App\Modules\WorkOs\Services\AuthPlatform\MFAEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

/**
 * LoginService — Enterprise Login Flow Orchestrator
 *
 * Centralises the entire login lifecycle so that individual controllers
 * (email/password, OAuth callback, magic-link, SSO) all go through
 * a single, audited, risk-aware entry point.
 *
 * Flow:
 *   1. Credential validation
 *   2. Brute-force / account lock check
 *   3. MFA challenge gate (if active)
 *   4. Session creation with device fingerprint + risk score
 *   5. Login attempt audit log
 */
class LoginService
{
    public function __construct(
        protected SessionEngine $sessionEngine,
        protected MFAEngine $mfaEngine,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Attempt email + password login.
     * Returns a structured LoginResult DTO.
     */
    public function attemptEmailPassword(Request $request): LoginResult
    {
        $email    = $request->input('email');
        $password = $request->input('password');
        $ip       = $request->ip();

        // 1. Lockout check
        if ($this->isLockedOut($email, $ip)) {
            return LoginResult::locked('Too many failed attempts. Please try again in 15 minutes.');
        }

        // 2. Find user
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            $this->recordFailedAttempt($email, $ip, 'invalid_credentials');
            return LoginResult::failed('These credentials do not match our records.');
        }

        // 3. Check MFA requirement
        if ($this->requiresMfa($user)) {
            // Don't create full session yet — return pending state
            $this->recordSuccessfulAttempt($email, $ip);
            return LoginResult::mfaRequired($user);
        }

        // 4. Full login
        return $this->completeLogin($user, $request);
    }

    /**
     * Complete login after MFA verification.
     * Called by MFAController::challenge() after successful code verification.
     */
    public function completeMfaLogin(User $user, string $code, Request $request): LoginResult
    {
        try {
            $valid = $this->mfaEngine->verifyLogin($user, $code);
        } catch (\Exception $e) {
            return LoginResult::failed('MFA verification error: ' . $e->getMessage());
        }

        if (!$valid) {
            $this->recordFailedAttempt($user->email, $request->ip(), 'mfa_failed');
            return LoginResult::failed('Invalid MFA code.');
        }

        return $this->completeLogin($user, $request);
    }

    /**
     * Finalise login for any auth method (OAuth, magic-link, SSO).
     * Creates the enterprise session + audit log.
     */
    public function completeLogin(User $user, Request $request): LoginResult
    {
        // Laravel session-based auth
        Auth::login($user, remember: $request->boolean('remember'));

        // Prevent session fixation
        $request->session()->regenerate();

        // Enterprise session record (device + geo + risk score)
        $authSession = $this->sessionEngine->createSession($user, $request);

        // Store opaque token in session for SecureSession middleware
        $request->session()->put('auth_session_token', $authSession->session_token);

        // Audit log
        $this->recordSuccessfulAttempt($user->email, $request->ip());

        return LoginResult::success($user, $authSession);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Is this email/IP combination locked out due to brute force?
     */
    protected function isLockedOut(string $email, string $ip): bool
    {
        $window = Carbon::now()->subMinutes(15);

        // Count failures in the past 15 minutes by IP OR email
        $failures = AuthLoginAttempt::where('created_at', '>=', $window)
            ->where('is_successful', false)
            ->where(function ($q) use ($email, $ip) {
                $q->where('email', $email)->orWhere('ip_address', $ip);
            })
            ->count();

        return $failures >= 10;
    }

    /**
     * Does this user have active MFA that must be challenged?
     */
    protected function requiresMfa(User $user): bool
    {
        return \App\Models\AuthMfa::where('user_id', $user->id)
            ->where('is_active', true)
            ->exists();
    }

    protected function recordFailedAttempt(string $email, string $ip, string $reason = 'invalid_credentials'): void
    {
        AuthLoginAttempt::create([
            'email'          => $email,
            'ip_address'     => $ip,
            'is_successful'  => false,
            'failure_reason' => $reason,
        ]);
    }

    protected function recordSuccessfulAttempt(string $email, string $ip): void
    {
        AuthLoginAttempt::create([
            'email'         => $email,
            'ip_address'    => $ip,
            'is_successful' => true,
        ]);
    }
}
