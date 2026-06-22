<?php

namespace App\Notifications\Trace;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class EventRegistrationConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $eventTitle,
        protected string $eventDate,
        protected int $eventId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'event_registration',
            'title' => 'Pendaftaran Event Berhasil',
            'message' => "Anda berhasil terdaftar di event \"{$this->eventTitle}\" pada {$this->eventDate}",
            'icon' => 'calendar-check',
            'href' => "/trace/events/{$this->eventId}",
        ];
    }
}
