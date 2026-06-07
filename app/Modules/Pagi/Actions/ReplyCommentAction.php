<?php

namespace App\Modules\Pagi\Actions;

use App\Models\User;
use App\Models\Pagi\PagiWork;
use App\Notifications\PagiNotification;
use Illuminate\Support\Str;

class ReplyCommentAction
{
    /**
     * Execute posting a reply to a comment.
     */
    public function execute(User $authUser, int $previewId, string $commentId, string $body, ?int $replyToUserId): array
    {
        $portfolio = PagiWork::findOrFail($previewId);

        $comments = $portfolio->comments ?? [];

        $newReply = [
            'id'         => uniqid(),
            'user_id'    => $authUser->id,
            'name'       => $authUser->name,
            'avatar'     => $authUser->foto_path
                ? (str_starts_with($authUser->foto_path, 'http') ? $authUser->foto_path : asset('storage/' . $authUser->foto_path))
                : null,
            'body'       => strip_tags($body),
            'created_at' => now()->toISOString(),
            'time'       => 'baru saja',
            'likes'      => [],
        ];

        $comments = array_map(function ($c) use ($commentId, $newReply) {
            if ($c['id'] === $commentId) {
                if (!isset($c['replies']) || !is_array($c['replies'])) {
                    $c['replies'] = [];
                }
                $c['replies'][] = $newReply;
            }
            return $c;
        }, $comments);

        $portfolio->update(['comments' => $comments]);

        // Notify target user (replied-to user) or original commenter
        $targetUserId = $replyToUserId;
        if (!$targetUserId) {
            $parentComment = collect($comments)->firstWhere('id', $commentId);
            if ($parentComment && isset($parentComment['user_id'])) {
                $targetUserId = $parentComment['user_id'];
            }
        }

        if ($targetUserId && $targetUserId !== $authUser->id) {
            $targetUser = User::find($targetUserId);
            if ($targetUser) {
                $avatar = $authUser->foto_path
                    ? (str_starts_with($authUser->foto_path, 'http') ? $authUser->foto_path : asset('storage/' . $authUser->foto_path))
                    : null;
                try {
                    $postOwnerName = $portfolio->user ? ($portfolio->user->pagi_username ?: $portfolio->user->name) : 'owner';
                    $targetUser->notify(new PagiNotification(
                        type: 'reply',
                        title: $authUser->pagi_username ?: $authUser->name,
                        message: 'membalas komentar Anda di postingan ' . $postOwnerName . ': "' . Str::limit($body, 30) . '"',
                        avatar: $avatar,
                        href: '/pagi/profile/' . $portfolio->user_id . '?project=' . $portfolio->id,
                        extra: [
                            'sender_id'    => $authUser->id,
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
