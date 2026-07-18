<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * ActivationEmail — Sent to users after admin approval (Case B).
 *
 * Contains a signed activation URL (valid 24 hours).
 * User clicks the link → OTP verification → password creation → activated.
 */
class ActivationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $userName;

    public string $userEmail;

    public string $activationUrl;

    /**
     * @param  User  $user  The newly created (not yet active) user
     * @param  string  $activationUrl  The signed activation URL
     */
    public function __construct(User $user, string $activationUrl)
    {
        $this->userName = $user->name;
        $this->userEmail = $user->email;
        $this->activationUrl = $activationUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Aktifkan Akun FMIKOM Portal Anda — Pendaftaran Disetujui',
            to: $this->userEmail,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.activation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
