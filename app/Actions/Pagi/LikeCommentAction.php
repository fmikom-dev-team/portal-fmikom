<?php

namespace App\Actions\Pagi;

use App\Models\User;
use App\Models\PagiWork;

class LikeCommentAction
{
    /**
     * Execute toggling likes on a comment.
     */
    public function execute(User $authUser, int $previewId, string $commentId): array
    {
        $portfolio = PagiWork::findOrFail($previewId);
        $comments = $portfolio->comments ?? [];
        
        $comments = array_map(function ($c) use ($commentId, $authUser) {
            if ($c['id'] === $commentId) {
                if (!isset($c['likes']) || !is_array($c['likes'])) {
                    $c['likes'] = [];
                }
                if (in_array($authUser->id, $c['likes'])) {
                    $c['likes'] = array_values(array_filter($c['likes'], fn($id) => $id !== $authUser->id));
                } else {
                    $c['likes'][] = $authUser->id;
                }
            }
            return $c;
        }, $comments);
        
        $portfolio->update(['comments' => $comments]);
        
        return $comments;
    }
}
