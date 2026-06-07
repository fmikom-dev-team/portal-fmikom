<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PagiMessagesRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $conversationId,
        public int $receiverId,
        public string $readAt,
        public int $senderId
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('pagi.chat.'.$this->conversationId),
            new PrivateChannel('App.Models.User.'.$this->senderId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'messages.read';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'receiver_id' => $this->receiverId,
            'read_at' => $this->readAt,
            'sender_id' => $this->senderId,
        ];
    }
}
