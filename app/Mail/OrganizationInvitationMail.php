<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrganizationInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $invitedEmail;
    public string $organizationName;
    public string $role;
    public string $invitedBy;
    public string $acceptUrl;
    public string $expiresAt;

    public function __construct(
        string $invitedEmail,
        string $organizationName,
        string $role,
        string $invitedBy,
        string $acceptUrl,
        string $expiresAt,
    ) {
        $this->invitedEmail     = $invitedEmail;
        $this->organizationName = $organizationName;
        $this->role             = $role;
        $this->invitedBy        = $invitedBy;
        $this->acceptUrl        = $acceptUrl;
        $this->expiresAt        = $expiresAt;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            to: $this->invitedEmail,
            subject: "You've been invited to join {$this->organizationName} on Portal FMIKOM",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.organization_invitation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
