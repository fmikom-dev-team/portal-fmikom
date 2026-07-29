<?php

namespace App\Events;

use App\Models\Pagi\PagiWork;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PagiWorkUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly PagiWork $work
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('pagi.works'),
        ];

        if ($this->work->user_id) {
            $channels[] = new PrivateChannel('App.Models.User.'.$this->work->user_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'PagiWorkUpdated';
    }

    public function broadcastWith(): array
    {
        $image = null;
        if ($this->work->cover_image) {
            $image = str_starts_with($this->work->cover_image, 'http')
                ? $this->work->cover_image
                : asset('storage/'.$this->work->cover_image);
        }

        return [
            'id' => $this->work->id,
            'user_id' => $this->work->user_id,
            'title' => $this->work->title,
            'image' => $image,
            'likes' => $this->work->likes_count ?? 0,
            'views' => $this->work->views_count ?? 0,
            'content' => $this->work->content,
            'is_published' => (bool) $this->work->is_published,
            'updated_at' => $this->work->updated_at?->toISOString(),
        ];
    }
}
