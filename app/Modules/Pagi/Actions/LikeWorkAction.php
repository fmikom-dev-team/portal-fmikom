<?php

namespace App\Modules\Pagi\Actions;

use App\Models\Pagi\PagiWork;
use App\Models\User;
use App\Notifications\PagiNotification;
use Illuminate\Support\Facades\Cache;

class LikeWorkAction
{
    /**
     * Execute toggling likes on a work project.
     */
    public function execute(User $authUser, int $previewId): array
    {
        $portfolio = PagiWork::findOrFail($previewId);

        $portfolio->likesRelation()->toggle($authUser->id);
        $isNowLiked = $portfolio->likesRelation()->where('user_id', $authUser->id)->exists();

        // Send real-time notification to the owner if liked & is not own project
        if ($isNowLiked && $portfolio->user_id !== $authUser->id) {
            $owner = $portfolio->user;
            if ($owner) {
                $avatar = $authUser->foto_path
                    ? (str_starts_with($authUser->foto_path, 'http') ? $authUser->foto_path : asset('storage/'.$authUser->foto_path))
                    : null;

                try {
                    $owner->notify(new PagiNotification(
                        type: 'like',
                        title: $authUser->pagi_username ?: $authUser->name,
                        message: 'menyukai postingan Anda: "'.($portfolio->title ?? 'Untitled Project').'"',
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

        // Invalidate public caches since the like count has changed
        Cache::forget('pagi_feed_projects_raw');
        for ($i = 1; $i <= 5; $i++) {
            Cache::forget("pagi_gallery_recommended_page_{$i}");
        }

        return [
            'liked' => $isNowLiked,
            'likes' => $portfolio->likesRelation()->count(),
        ];
    }
}
