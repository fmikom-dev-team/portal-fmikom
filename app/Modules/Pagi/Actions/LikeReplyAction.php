<?php

namespace App\Modules\Pagi\Actions;

use App\Models\Pagi\PagiWork;
use App\Models\Pagi\PagiWorkComment;
use App\Models\User;

class LikeReplyAction
{
    /**
     * Execute toggling likes on a reply.
     */
    public function execute(User $authUser, int $previewId, string $commentId, string $replyId): array
    {
        $reply = PagiWorkComment::where('uuid', $replyId)->firstOrFail();

        $reply->likesRelation()->toggle($authUser->id);

        // Return fresh comments with full eager-load (avoids ->fresh()->comments N+1)
        return $this->loadFormattedComments($previewId);
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
