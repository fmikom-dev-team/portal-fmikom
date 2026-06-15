<?php

namespace App\Mail\Trace;

use App\Models\Tracer\Kuesioner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class KuesionerReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $userName,
        public Kuesioner $kuesioner
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Pengisian Kuesioner: {$this->kuesioner->judul}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.trace.kuesioner-reminder',
        );
    }
}
