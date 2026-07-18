<?php

namespace App\Enums;

enum UserAccountStatus: string
{
    // Full lifecycle states stored in status_approval column
    case Pending = 'pending';      // Self-registered, waiting admin review
    case Approved = 'approved';     // Admin approved, activation email sent
    case OtpSent = 'otp_sent';     // OTP/activation token sent to email
    case OtpVerified = 'otp_verified'; // OTP verified, waiting for password creation
    case Activated = 'activated';    // Password created, fully active
    case Rejected = 'rejected';     // Registration rejected by admin
    case Suspended = 'suspended';    // Account suspended by admin
    case Expired = 'expired';      // Activation token expired without action

    /**
     * Whether the user in this state can login.
     */
    public function canLogin(): bool
    {
        return in_array($this, [self::Activated]);
    }

    /**
     * Whether this is a blocking state that prevents login with a specific message.
     */
    public function loginBlockMessage(): ?string
    {
        return match ($this) {
            self::Pending => 'Pendaftaran Anda sedang dalam review oleh admin. Mohon tunggu.',
            self::Approved => 'Akun Anda telah disetujui. Silakan cek email untuk link aktivasi.',
            self::OtpSent => 'Kode aktivasi telah dikirimkan ke email Anda. Silakan cek inbox.',
            self::OtpVerified => 'Identitas terverifikasi. Silakan buat password untuk melanjutkan.',
            self::Rejected => 'Maaf, pendaftaran Anda telah ditolak. Hubungi admin untuk informasi lebih lanjut.',
            self::Suspended => 'Akun Anda telah ditangguhkan. Hubungi administrator.',
            self::Expired => 'Token aktivasi Anda telah kedaluwarsa. Hubungi admin untuk kirim ulang.',
            self::Activated => null, // No block message
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Review',
            self::Approved => 'Disetujui',
            self::OtpSent => 'OTP Terkirim',
            self::OtpVerified => 'OTP Terverifikasi',
            self::Activated => 'Aktif',
            self::Rejected => 'Ditolak',
            self::Suspended => 'Ditangguhkan',
            self::Expired => 'Kedaluwarsa',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'yellow',
            self::Approved => 'blue',
            self::OtpSent => 'purple',
            self::OtpVerified => 'indigo',
            self::Activated => 'green',
            self::Rejected => 'red',
            self::Suspended => 'orange',
            self::Expired => 'gray',
        };
    }
}
