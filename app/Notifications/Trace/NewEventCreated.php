<?php

namespace App\Notifications\Trace;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewEventCreated extends Notification
{
    use Queueable;

    public function __construct(
        private string $eventTitle,
        private string $eventDate,
        private int $eventId
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'new_event',
            'title' => 'Event Baru',
            'message' => "Event baru: {$this->eventTitle} pada {$this->eventDate}",
            'icon' => 'calendar-plus',
            'href' => "/trace/events/{$this->eventId}",
        ];
    }
}
