<?php

namespace App\Mail\Trace;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyJobDigest extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public $newJobs,
        public $newEvents,
        public int $weekNumber
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Ringkasan Minggu Ini — {$this->newJobs->count()} Lowongan & {$this->newEvents->count()} Event Baru",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.trace.weekly-digest',
        );
    }
}
