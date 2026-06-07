<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PagiMessageEdited implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $messageId,
        public string $conversationId,
        public string $newBody,
        public string $editedAt
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('pagi.chat.'.$this->conversationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.edited';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->messageId,
            'body' => $this->newBody,
            'edited_at' => $this->editedAt,
        ];
    }
}
