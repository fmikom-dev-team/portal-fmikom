<?php

namespace App\Modules\WorkOs\Services\Auth;

use App\Models\Auth\AuthSession;
use App\Models\User;

/**
 * LoginResult — Value Object / DTO
 *
 * A structured, type-safe return value from LoginService.
 * Controllers check ->status and branch accordingly — no boolean soup.
 *
 * Usage:
 *   $result = $loginService->attemptEmailPassword($request);
 *   match ($result->status) {
 *       'success'      => redirect dashboard,
 *       'mfa_required' => redirect to MFA challenge page,
 *       'failed'       => back with error,
 *       'locked'       => back with throttle error,
 *   }
 */
class LoginResult
{
    private function __construct(
        public readonly string $status,         // 'success' | 'failed' | 'mfa_required' | 'locked'
        public readonly ?User $user = null,
        public readonly ?AuthSession $session = null,
        public readonly ?string $message = null,
    ) {}

    // ─── Factory Methods ──────────────────────────────────────────────────────

    public static function success(User $user, AuthSession $session): self
    {
        return new self(status: 'success', user: $user, session: $session);
    }

    public static function mfaRequired(User $user): self
    {
        return new self(
            status: 'mfa_required',
            user: $user,
            message: 'Multi-factor authentication required.',
        );
    }

    public static function failed(string $message): self
    {
        return new self(status: 'failed', message: $message);
    }

    public static function locked(string $message): self
    {
        return new self(status: 'locked', message: $message);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function requiresMfa(): bool
    {
        return $this->status === 'mfa_required';
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }

    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }
}
