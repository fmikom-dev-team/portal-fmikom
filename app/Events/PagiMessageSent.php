<?php

namespace App\Events;

use App\Models\Pagi\PagiMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PagiMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public PagiMessage $message) {}

    /**
     * Broadcast on the private channel for this conversation.
     * Only the two participants can subscribe (enforced in channels.php).
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('pagi.chat.'.$this->message->conversation_id),
            new PrivateChannel('App.Models.User.'.$this->message->receiver_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Only expose safe data — never expose sender's full user record.
     */
    public function broadcastWith(): array
    {
        $parentData = null;
        if ($this->message->parent_id && $this->message->parent) {
            $parent = $this->message->parent;
            $parentData = [
                'id' => $parent->id,
                'body' => $parent->body,
                'sender' => [
                    'id' => $parent->sender->id,
                    'name' => $parent->sender->name,
                ],
            ];
        }

        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'parent_id' => $this->message->parent_id,
            'parent' => $parentData,
            'body' => $this->message->body,
            'created_at' => $this->message->created_at->toISOString(),
            'sender' => [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->name,
                'foto_path' => $this->message->sender->foto_path ?? null,
                'metadata' => $this->message->sender->metadata,
            ],
        ];
    }
}
