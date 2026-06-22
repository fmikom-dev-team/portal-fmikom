<?php

namespace App\Notifications\Trace;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewEventRegistration extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $alumniName,
        protected string $eventTitle,
        protected int $eventId,
        protected int $currentCount,
        protected ?int $maxParticipants,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $countInfo = $this->maxParticipants
            ? " ({$this->currentCount}/{$this->maxParticipants})"
            : " ({$this->currentCount} peserta)";

        return [
            'type' => 'event_registration',
            'title' => 'Pendaftar Event Baru',
            'message' => "{$this->alumniName} mendaftar event \"{$this->eventTitle}\"{$countInfo}",
            'icon' => 'user-plus',
            'href' => "/trace/admin/events/{$this->eventId}",
        ];
    }
}
