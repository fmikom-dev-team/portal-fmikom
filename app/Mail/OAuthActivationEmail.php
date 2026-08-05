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
 * OAuthActivationEmail — Sent to OAuth users after admin approval.
 *
 * Contains a signed activation URL pointing to the Smart Access Control verification page.
 * User clicks the link → Smart Access Control animated verification → Auto-login to Dashboard.
 */
class OAuthActivationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $userName;

    public string $userEmail;

    public string $providerName;

    public string $activationUrl;

    /**
     * @param  User  $user  The newly approved OAuth user
     * @param  string  $activationUrl  The signed Smart Access verification URL
     * @param  string  $providerName  Name of the OAuth provider (Google, Microsoft, GitHub, Apple)
     */
    public function __construct(User $user, string $activationUrl, string $providerName = 'OAuth Provider')
    {
        $this->userName = $user->name;
        $this->userEmail = $user->email;
        $this->providerName = ucfirst($providerName);
        $this->activationUrl = $activationUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Akses Disetujui — Verifikasi Akun '.$this->providerName.' FMIKOM Portal Anda',
            to: $this->userEmail,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.oauth-activation-email',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
