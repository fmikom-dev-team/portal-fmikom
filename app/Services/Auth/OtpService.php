<?php

namespace App\Services\Auth;

use App\Enums\OtpPurpose;
use App\Mail\SendOtpEmail;
use App\Models\Auth\AuthOtpToken;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

/**
 * OtpService — Centralized OTP generation and verification.
 *
 * Single responsibility: manages the full lifecycle of OTP tokens,
 * including generation, email delivery (via queue), and verification
 * with brute-force protection.
 */
class OtpService
{
    /**
     * Generate a new OTP and send it to the specified email.
     *
     * Automatically invalidates any previous active OTP for the same email+purpose.
     * Email is dispatched via queue.
     *
     * @param  int|null  $userId  Null for pre-user OTPs (e.g. identity verification step)
     * @param  string  $email  Destination email
     * @param  User|null  $userForDisplay  User object for personalized email (name, etc.)
     * @return AuthOtpToken The created token model
     */
    public function generate(
        ?int $userId,
        string $email,
        OtpPurpose $purpose,
        ?User $userForDisplay = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
    ): AuthOtpToken {
        $result = AuthOtpToken::generate(
            userId: $userId,
            email: $email,
            purpose: $purpose,
            ipAddress: $ipAddress,
            userAgent: $userAgent,
        );

        $otpModel = $result['model'];
        $plaintext = $result['plaintext'];

        // Queue email delivery
        try {
            $displayUser = $userForDisplay ?? ($userId ? User::find($userId, ['*']) : null);
            Mail::to($email)->queue(new SendOtpEmail($displayUser, $plaintext, $purpose));
        } catch (\Throwable $e) {
            Log::error('[OtpService] Gagal mengirim OTP ke '.$email.': '.$e->getMessage(), [
                'purpose' => $purpose->value,
                'user_id' => $userId,
            ]);
        }

        // DEV ONLY: Log plaintext OTP for local debugging
        if (app()->isLocal()) {
            Log::info('[DEV ONLY] OTP untuk '.$email.' ('.$purpose->value.'): '.$plaintext);
        }

        return $otpModel;
    }

    /**
     * Verify a plaintext OTP for a given email+purpose.
     * Returns the token model on success, throws on failure.
     *
     * @throws \RuntimeException with a user-facing message
     */
    public function verify(string $email, OtpPurpose $purpose, string $plaintext): AuthOtpToken
    {
        // Rate limit per email+purpose to prevent enumeration
        $rateLimitKey = 'otp_verify:'.$purpose->value.':'.hash('sha256', $email);
        if (RateLimiter::tooManyAttempts($rateLimitKey, maxAttempts: 10)) {
            throw new \RuntimeException('Terlalu banyak percobaan. Silakan tunggu sebelum mencoba lagi.');
        }
        RateLimiter::hit($rateLimitKey, decaySeconds: 60);

        $token = AuthOtpToken::findActive($email, $purpose);

        if (! $token) {
            throw new \RuntimeException('Kode OTP tidak ditemukan atau sudah kedaluwarsa. Silakan minta kode baru.');
        }

        $result = $token->verify($plaintext);

        return match ($result) {
            'ok' => $token,
            'expired' => throw new \RuntimeException('Kode OTP sudah kedaluwarsa. Silakan minta ulang.'),
            'used' => throw new \RuntimeException('Kode OTP ini sudah pernah digunakan.'),
            'locked' => throw new \RuntimeException('Terlalu banyak percobaan salah. Silakan minta kode OTP baru.'),
            'invalid' => throw new \RuntimeException(
                'Kode OTP salah atau tidak valid. '.
                'Sisa percobaan: '.$token->remainingAttempts().'.'
            ),
            default => throw new \RuntimeException('Verifikasi OTP gagal.'),
        };
    }

    /**
     * Check if OTP resend is allowed (not too recently sent).
     * Returns true if resend is allowed, false if too early.
     */
    public function canResend(string $email, OtpPurpose $purpose): bool
    {
        $existing = AuthOtpToken::findActive($email, $purpose);

        if (! $existing) {
            return true; // No active OTP → allow send
        }

        // Block if OTP was created less than 2 minutes ago
        $ageInSeconds = $existing->created_at->diffInSeconds(now());

        return $ageInSeconds >= 120; // 2 minutes
    }

    /**
     * Clean up expired and used OTP tokens (for scheduled jobs).
     */
    public function pruneExpired(): int
    {
        $expiredCount = AuthOtpToken::where('expires_at', '<', now()->subHour(), 'and')->delete();

        $usedCount = AuthOtpToken::where('is_used', '=', true, 'and')
            ->where('used_at', '<', now()->subDay(), 'and')
            ->delete();

        return $expiredCount + $usedCount;
    }
}
