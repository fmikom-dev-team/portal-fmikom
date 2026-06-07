<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PagiUnreadCountUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public int $unreadCount
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('App.Models.User.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'unread.count.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'unread_messages_count' => $this->unreadCount,
        ];
    }
}
