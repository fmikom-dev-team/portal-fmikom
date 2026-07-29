<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PagiWorkDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $workId,
        public readonly int $userId
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('pagi.works'),
        ];

        if ($this->userId) {
            $channels[] = new PrivateChannel('App.Models.User.'.$this->userId);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'PagiWorkDeleted';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->workId,
            'user_id' => $this->userId,
        ];
    }
}
