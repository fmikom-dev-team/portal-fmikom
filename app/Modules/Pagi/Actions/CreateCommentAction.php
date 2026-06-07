<?php

namespace App\Modules\Pagi\Actions;

use App\Models\Pagi\PagiWork;
use App\Models\User;
use App\Notifications\PagiNotification;
use Illuminate\Support\Str;

class CreateCommentAction
{
    /**
     * Execute posting a new comment.
     */
    public function execute(User $authUser, int $previewId, string $body): array
    {
        $portfolio = PagiWork::findOrFail($previewId);

        $avatar = null;
        if ($authUser->foto_path) {
            $avatar = str_starts_with($authUser->foto_path, 'http')
                ? $authUser->foto_path
                : asset('storage/'.$authUser->foto_path);
        }

        $comments = $portfolio->comments ?? [];
        $newComment = [
            'id' => uniqid(),
            'user_id' => $authUser->id,
            'name' => $authUser->name,
            'pagi_username' => $authUser->pagi_username,
            'avatar' => $avatar,
            'body' => strip_tags($body),
            'created_at' => now()->toISOString(),
            'time' => 'baru saja',
            'likes' => [],
        ];

        $comments[] = $newComment;
        $portfolio->update(['comments' => $comments]);

        // Send real-time notification to the owner if commented & is not own project
        if ($portfolio->user_id !== $authUser->id) {
            $owner = $portfolio->user;
            if ($owner) {

                try {
                    $owner->notify(new PagiNotification(
                        type: 'comment',
                        title: $authUser->pagi_username ?: $authUser->name,
                        message: 'mengomentari postingan Anda: "'.Str::limit($body, 30).'"',
                        avatar: $avatar,
                        href: '/pagi/profile/'.$portfolio->user_id.'?project='.$portfolio->id,
                        extra: [
                            'sender_id' => $authUser->id,
                            'portfolio_id' => $portfolio->id,
                        ],
                    ));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return $comments;
    }
}
