<?php

namespace App\Modules\Pagi\Actions;

use App\Models\Pagi\PagiWork;
use App\Models\Pagi\PagiWorkComment;
use App\Models\User;
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

        $parentCommentRecord = PagiWorkComment::where('uuid', $commentId)->firstOrFail();

        PagiWorkComment::create([
            'uuid'      => (string) Str::uuid(),
            'work_id'   => $portfolio->id,
            'user_id'   => $authUser->id,
            'parent_id' => $parentCommentRecord->id,
            'body'      => strip_tags($body),
        ]);

        // Notify target user (replied-to user) or original commenter
        $targetUserId = $replyToUserId ?? $parentCommentRecord->user_id;

        if ($targetUserId && $targetUserId !== $authUser->id) {
            /** @var User|null $targetUser */
            $targetUser = User::query()->find($targetUserId);
            if ($targetUser) {
                $avatar = null;
                if ($authUser->foto_path) {
                    $avatar = str_starts_with($authUser->foto_path, 'http')
                        ? $authUser->foto_path
                        : asset('storage/' . $authUser->foto_path);
                }
                try {
                    $postOwnerName = 'owner';
                    if ($portfolio->user) {
                        $postOwnerName = $portfolio->user->pagi_username ?: $portfolio->user->name;
                    }
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

        // Return fresh comments with full eager-load (avoids ->fresh()->comments N+1)
        return $this->loadFormattedComments($portfolio->id);
    }

    /**
     * Load all comments for a work with full eager-loading, returning formatted array.
     */
    private function loadFormattedComments(int $workId): array
    {
        $work = PagiWork::with([
            'commentsRelation' => fn ($q) => $q->whereNull('parent_id'),
            'commentsRelation.user:id,name,pagi_username,foto_path',
            'commentsRelation.likesRelation',
            'commentsRelation.replies.user:id,name,pagi_username,foto_path',
            'commentsRelation.replies.likesRelation',
        ])->findOrFail($workId);

        return $work->comments;
    }
}
