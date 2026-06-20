<?php

namespace App\Modules\Pagi\Actions;

use App\Models\Pagi\PagiWork;
use App\Models\Pagi\PagiWorkComment;
use App\Models\User;

class LikeCommentAction
{
    /**
     * Execute toggling likes on a comment.
     */
    public function execute(User $authUser, int $previewId, string $commentId): array
    {
        // Validate that this comment belongs to the given work
        $comment = PagiWorkComment::where('uuid', $commentId)->firstOrFail();

        $comment->likesRelation()->toggle($authUser->id);

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
