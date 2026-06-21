<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WorkOsAlert extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $title,
        public string $description,
        public string $severity, // 'error', 'warning', 'success', 'info'
        public array $extra = []
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'severity' => $this->severity,
            'extra' => $this->extra,
        ];
    }
}
