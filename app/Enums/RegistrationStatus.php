<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case OtpSent = 'otp_sent';
    case OtpVerified = 'otp_verified';
    case Activated = 'activated';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Review',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
            self::OtpSent => 'OTP Terkirim',
            self::OtpVerified => 'OTP Terverifikasi',
            self::Activated => 'Teraktivasi',
            self::Expired => 'Kedaluwarsa',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Approved => 'blue',
            self::Rejected => 'red',
            self::OtpSent => 'purple',
            self::OtpVerified => 'indigo',
            self::Activated => 'green',
            self::Expired => 'gray',
        };
    }

    /**
     * Terminal states that cannot transition further.
     */
    public function isTerminal(): bool
    {
        return in_array($this, [self::Rejected, self::Activated]);
    }

    /**
     * States where the user is considered "in progress" (not yet active).
     */
    public function isPending(): bool
    {
        return in_array($this, [self::Pending, self::Approved, self::OtpSent, self::OtpVerified]);
    }
}
