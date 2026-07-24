<?php

namespace App\Enums;

enum OtpPurpose: string
{
    case EmailVerification = 'email_verification';
    case AccountActivation = 'account_activation';
    case PasswordReset = 'password_reset';
    case MagicLink = 'magic_link';

    public function label(): string
    {
        return match ($this) {
            self::EmailVerification => 'Verifikasi Email',
            self::AccountActivation => 'Aktivasi Akun',
            self::PasswordReset => 'Reset Password',
            self::MagicLink => 'Magic Link Login',
        };
    }

    /**
     * TTL in minutes for each purpose.
     */
    public function ttlMinutes(): int
    {
        return match ($this) {
            self::EmailVerification => 15,
            self::AccountActivation => 60,    // 1 jam untuk email aktivasi
            self::PasswordReset => 15,
            self::MagicLink => 15,
        };
    }

    /**
     * Maximum OTP verification attempts before lockout.
     */
    public function maxAttempts(): int
    {
        return match ($this) {
            self::EmailVerification => 5,
            self::AccountActivation => 5,
            self::PasswordReset => 5,
            self::MagicLink => 1,  // One-time use
        };
    }
}
