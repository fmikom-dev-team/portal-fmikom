<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PagiMessageDeleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $messageId,
        public string $conversationId,
        public int $senderId,
        public int $receiverId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('pagi.chat.'.$this->conversationId),
            new PrivateChannel('App.Models.User.'.$this->senderId),
            new PrivateChannel('App.Models.User.'.$this->receiverId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->messageId,
            'conversation_id' => $this->conversationId,
        ];
    }
}
