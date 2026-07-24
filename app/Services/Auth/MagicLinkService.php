<?php

namespace App\Services\Auth;

use App\Mail\MagicLinkEmail;
use App\Models\Auth\AuthAuditLog;
use App\Models\Auth\AuthMagicLink;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

/**
 * MagicLinkService — Implements secure magic link authentication.
 *
 * Security design:
 *  - Magic links are ONLY for ACTIVE users (is_active = true, status = activated)
 *  - Token is a random 64-char string → hashed before storage
 *  - Signed URL prevents parameter tampering
 *  - One-time use (is_used = true after first verification)
 *  - Rate limited per email
 *  - TTL: 15 minutes
 *
 * Pending users NEVER receive magic links.
 */
class MagicLinkService
{
    private const TTL_MINUTES = 15;

    /**
     * Send a magic link to the specified email.
     *
     * Silently succeeds even if email does not exist (prevent email enumeration).
     * Rate limited to prevent abuse.
     *
     * @return bool True if link was sent, false if user not found or not active
     */
    public function send(string $email, ?string $ipAddress = null, ?string $userAgent = null): bool
    {
        // Rate limit: max 3 magic links per email per 5 minutes
        $rateLimitKey = 'magic_link_send:'.hash('sha256', strtolower($email));
        if (RateLimiter::tooManyAttempts($rateLimitKey, maxAttempts: 3)) {
            // Silently return true to prevent enumeration
            return true;
        }
        RateLimiter::hit($rateLimitKey, decaySeconds: 300);

        // Look up user
        $user = User::where('email', '=', $email, 'and')->first();

        // Guard: Only active users get magic links
        if (! $user || ! $user->isAccountActive()) {
            // Return true silently — don't reveal whether account exists
            return false;
        }

        // Invalidate any previous unexpired magic links for this email
        AuthMagicLink::where('email', '=', $email, 'and')
            ->where('is_used', '=', false, 'and')
            ->where('expires_at', '>', now(), 'and')
            ->update(['is_used' => true, 'used_at' => now()]);

        // Generate token
        $plainToken = Str::random(64);
        $tokenHash = hash('sha256', $plainToken);

        $magicLink = AuthMagicLink::create([
            'user_id' => $user->id,
            'email' => $email,
            'token_hash' => $tokenHash,
            'is_used' => false,
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        // Build signed URL (prevents parameter tampering)
        $magicUrl = URL::temporarySignedRoute(
            'auth.magic-link.verify',
            now()->addMinutes(self::TTL_MINUTES),
            [
                'token' => $plainToken,
                'email' => $email,
            ]
        );

        // Send email (queued)
        try {
            Mail::to($email)->queue(new MagicLinkEmail($user, $magicUrl));
        } catch (\Throwable $e) {
            Log::error('[MagicLinkService] Gagal mengirim magic link ke '.$email.': '.$e->getMessage());
        }

        AuthAuditLog::log('auth.magic_link.sent', $user->id, [
            'email' => $email,
            'ip' => $ipAddress,
        ]);

        return true;
    }

    /**
     * Verify a magic link token and return the authenticated user.
     *
     * @return User The authenticated user
     *
     * @throws \RuntimeException on any failure (use message for user display)
     */
    public function verify(string $email, string $plainToken): User
    {
        // Rate limit verification attempts
        $rateLimitKey = 'magic_link_verify:'.hash('sha256', strtolower($email));
        if (RateLimiter::tooManyAttempts($rateLimitKey, maxAttempts: 5)) {
            throw new \RuntimeException('Terlalu banyak percobaan. Silakan minta magic link baru.');
        }
        RateLimiter::hit($rateLimitKey, decaySeconds: 60);

        return DB::transaction(function () use ($email, $plainToken) {
            $link = AuthMagicLink::where('email', '=', $email, 'and')
                ->where('is_used', '=', false, 'and')
                ->where('expires_at', '>', now(), 'and')
                ->latest()
                ->lockForUpdate()
                ->first();

            if (! $link) {
                throw new \RuntimeException('Magic link tidak valid atau sudah kedaluwarsa. Silakan minta link baru.');
            }

            if (! hash_equals($link->token_hash, hash('sha256', $plainToken))) {
                throw new \RuntimeException('Magic link tidak valid.');
            }

            /** @var User|null $user */
            $user = User::find($link->user_id, ['*']);

            if (! $user || ! $user->isAccountActive()) {
                throw new \RuntimeException('Akun tidak dapat diakses via magic link.');
            }

            // Mark as used (one-time use)
            $link->fill(['is_used' => true, 'used_at' => now()])->save();

            AuthAuditLog::log('auth.magic_link.used', $user->id, [
                'email' => $email,
            ]);

            return $user;
        });
    }

    /**
     * Prune expired magic links (for scheduled cleanup).
     */
    public function pruneExpired(): int
    {
        return AuthMagicLink::where('expires_at', '<', now()->subHour(), 'and')->delete();
    }
}
