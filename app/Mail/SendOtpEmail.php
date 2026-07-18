<?php

namespace App\Mail;

use App\Enums\OtpPurpose;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendOtpEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $otpCode;

    public string $userName;

    public string $userEmail;

    public string $purposeLabel;

    public string $subjectLine;

    /**
     * Create a new message instance.
     * Menerima data primitif (bukan model) agar queue tidak bergantung DB saat proses job.
     *
     * @param  User|null  $user  Null untuk pre-user OTPs (identity verification)
     * @param  string  $otpCode  Plaintext OTP (NEVER store this — only pass to email)
     * @param  OtpPurpose  $purpose  Context for subject line and message body
     */
    public function __construct(?User $user, string $otpCode, OtpPurpose $purpose = OtpPurpose::EmailVerification)
    {
        $this->userName = $user?->name ?? 'Pengguna';
        $this->userEmail = $user?->email ?? '';
        $this->otpCode = $otpCode;
        $this->purposeLabel = $purpose->label();

        $this->subjectLine = match ($purpose) {
            OtpPurpose::AccountActivation => 'Kode Aktivasi Akun FMIKOM Portal',
            OtpPurpose::PasswordReset => 'Kode Reset Password Akun Portal FMIKOM',
            OtpPurpose::MagicLink => 'Kode Login Akun Portal FMIKOM',
            default => 'Kode Verifikasi Keamanan Akun Portal FMIKOM',
        };
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
            to: $this->userEmail,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.send_otp',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
