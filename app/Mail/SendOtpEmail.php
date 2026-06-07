<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class SendOtpEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $otpCode;
    public string $userName;
    public string $userEmail;

    /**
     * Create a new message instance.
     * Menerima data primitif (bukan model) agar queue tidak bergantung DB saat proses job.
     */
    public function __construct(User $user, string $otpCode)
    {
        $this->userName  = $user->name;
        $this->userEmail = $user->email;
        $this->otpCode   = $otpCode;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Verifikasi Keamanan Akun Portal FMIKOM',
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
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
