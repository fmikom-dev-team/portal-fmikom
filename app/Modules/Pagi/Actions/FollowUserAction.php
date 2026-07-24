<?php

namespace App\Modules\Pagi\Actions;

use App\Models\Pagi\PagiFollow;
use App\Models\User;
use App\Notifications\PagiNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FollowUserAction
{
    /**
     * Execute follow/unfollow action.
     * Returns array with target follow status and new follower count.
     */
    public function execute(User $authUser, int $targetUserId): array
    {
        $targetUser = User::findOrFail($targetUserId);

        if ($authUser->id === $targetUser->id) {
            throw new \InvalidArgumentException('Cannot follow yourself');
        }

        $isNowFollowing = false;

        DB::transaction(function () use ($authUser, $targetUser, &$isNowFollowing) {
            $exists = PagiFollow::query()->where('follower_id', '=', $authUser->id, 'and')
                ->where('following_id', '=', $targetUser->id, 'and')
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                PagiFollow::query()->where('follower_id', '=', $authUser->id, 'and')
                    ->where('following_id', '=', $targetUser->id, 'and')
                    ->delete();
                $isNowFollowing = false;
            } else {
                PagiFollow::query()->firstOrCreate([
                    'follower_id' => $authUser->id,
                    'following_id' => $targetUser->id,
                ]);
                $isNowFollowing = true;
            }
        });

        $followersCount = count(PagiFollow::query()->where('following_id', '=', $targetUser->id, 'and')->get());

        // Bust public profile caches so isFollowing and followers_count are accurate on refresh
        Cache::forget("pagi_public_profile_{$targetUser->id}");
        Cache::forget("pagi_public_profile_{$authUser->id}");

        // Send real-time notification if following
        if ($isNowFollowing) {
            $avatar = null;
            if ($authUser->foto_path) {
                $avatar = str_starts_with($authUser->foto_path, 'http') ? $authUser->foto_path : asset('storage/'.$authUser->foto_path);
            }

            try {
                $targetUser->notify(new PagiNotification(
                    type: 'follow',
                    title: $authUser->pagi_username ?: $authUser->name,
                    message: 'mulai mengikuti Anda.',
                    avatar: $avatar,
                    href: '/pagi/profile/'.$authUser->id,
                    extra: ['sender_id' => $authUser->id],
                ));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return [
            'following' => $isNowFollowing,
            'followers_count' => $followersCount,
        ];
    }
}
