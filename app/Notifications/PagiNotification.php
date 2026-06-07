<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PagiNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    /**
     * @param string $type     'like' | 'follow' | 'comment' | 'system'
     * @param string $title    Actor name or system title
     * @param string $message  Human-readable description
     * @param string|null $avatar   URL to actor's avatar
     * @param string|null $href     Link to navigate on click
     * @param array  $extra    Any extra data
     */
    public function __construct(
        public readonly string  $type,
        public readonly string  $title,
        public readonly string  $message,
        public readonly ?string $avatar = null,
        public readonly ?string $href   = null,
        public readonly array   $extra  = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->payload();
    }

    public function toBroadcast(object $notifiable): array
    {
        return $this->payload();
    }


    private function payload(): array
    {
        return [
            'type'    => $this->type,
            'title'   => $this->title,
            'message' => $this->message,
            'avatar'  => $this->avatar,
            'href'    => $this->href ?? '/pagi',
            ...$this->extra,
        ];
    }
}
