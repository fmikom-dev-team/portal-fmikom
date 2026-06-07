<?php

namespace App\Modules\Pagi\Actions;

use App\Models\User;
use App\Notifications\PagiNotification;

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

        // Update auth user's "following" list
        $authMeta = $authUser->metadata ?? [];
        $following = $authMeta['following'] ?? [];
        $isNowFollowing = !in_array($targetUser->id, $following);

        if ($isNowFollowing) {
            $following[] = $targetUser->id;
        } else {
            $following = array_values(array_filter($following, fn($id) => $id !== $targetUser->id));
        }
        $authMeta['following'] = $following;
        $authUser->update(['metadata' => $authMeta]);

        // Update target user's "followers" list
        $targetMeta = $targetUser->metadata ?? [];
        $followers = $targetMeta['followers'] ?? [];

        if ($isNowFollowing) {
            if (!in_array($authUser->id, $followers)) {
                $followers[] = $authUser->id;
            }
        } else {
            $followers = array_values(array_filter($followers, fn($id) => $id !== $authUser->id));
        }
        $targetMeta['followers'] = $followers;
        $targetUser->update(['metadata' => $targetMeta]);

        // Send real-time notification if following
        if ($isNowFollowing) {
            $avatar = null;
            if ($authUser->foto_path) {
                $avatar = str_starts_with($authUser->foto_path, 'http') ? $authUser->foto_path : asset('storage/' . $authUser->foto_path);
            }

            try {
                $targetUser->notify(new PagiNotification(
                    type: 'follow',
                    title: $authUser->pagi_username ?: $authUser->name,
                    message: 'mulai mengikuti Anda.',
                    avatar: $avatar,
                    href: '/pagi/profile/' . $authUser->id,
                    extra: ['sender_id' => $authUser->id],
                ));
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return [
            'following' => $isNowFollowing,
            'followers_count' => count($followers),
        ];
    }
}
