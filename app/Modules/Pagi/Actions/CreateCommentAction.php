<?php

namespace App\Modules\Pagi\Actions;

use App\Models\Pagi\PagiWork;
use App\Models\Pagi\PagiWorkComment;
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
                : asset('storage/' . $authUser->foto_path);
        }

        // Create comment in normalized table
        PagiWorkComment::create([
            'uuid'      => (string) Str::uuid(),
            'work_id'   => $portfolio->id,
            'user_id'   => $authUser->id,
            'parent_id' => null,
            'body'      => strip_tags($body),
        ]);

        // Send real-time notification to the owner if commented & is not own project
        if ($portfolio->user_id !== $authUser->id) {
            $owner = $portfolio->user;
            if ($owner) {
                try {
                    $owner->notify(new PagiNotification(
                        type: 'comment',
                        title: $authUser->pagi_username ?: $authUser->name,
                        message: 'mengomentari postingan Anda: "' . Str::limit($body, 30) . '"',
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

        // Return fresh comments with full eager-load in a single set of queries
        // (avoids ->fresh()->comments which triggers N+1 inside the accessor)
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
