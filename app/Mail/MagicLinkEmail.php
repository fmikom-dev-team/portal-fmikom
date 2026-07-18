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
 * MagicLinkEmail — Sent when user requests a magic link login.
 *
 * Contains a signed URL (15 minutes TTL).
 * Only sent to ACTIVE users.
 */
class MagicLinkEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $userName;

    public string $userEmail;

    public string $magicUrl;

    public function __construct(User $user, string $magicUrl)
    {
        $this->userName = $user->name;
        $this->userEmail = $user->email;
        $this->magicUrl = $magicUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Link Login Akun FMIKOM Portal Anda',
            to: $this->userEmail,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.magic_link',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
