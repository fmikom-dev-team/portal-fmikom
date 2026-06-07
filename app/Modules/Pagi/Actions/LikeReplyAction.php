<?php

namespace App\Modules\Pagi\Actions;

use App\Models\User;
use App\Models\PagiWork;

class LikeReplyAction
{
    /**
     * Execute toggling likes on a reply.
     */
    public function execute(User $authUser, int $previewId, string $commentId, string $replyId): array
    {
        $portfolio = PagiWork::findOrFail($previewId);
        $comments = $portfolio->comments ?? [];

        $comments = array_map(function ($c) use ($commentId, $replyId, $authUser) {
            if ($c['id'] === $commentId && !empty($c['replies'])) {
                $c['replies'] = array_map(function ($r) use ($replyId, $authUser) {
                    if ($r['id'] === $replyId) {
                        if (!isset($r['likes']) || !is_array($r['likes'])) {
                            $r['likes'] = [];
                        }
                        if (in_array($authUser->id, $r['likes'])) {
                            $r['likes'] = array_values(array_filter($r['likes'], fn($id) => $id !== $authUser->id));
                        } else {
                            $r['likes'][] = $authUser->id;
                        }
                    }
                    return $r;
                }, $c['replies']);
            }
            return $c;
        }, $comments);

        $portfolio->update(['comments' => $comments]);

        return $comments;
    }
}
