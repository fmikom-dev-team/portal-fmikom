<?php

namespace App\Mail\Trace;

use App\Models\Tracer\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public Event $event
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reminder: Event {$this->event->title} besok!",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.trace.event-reminder',
        );
    }
}
