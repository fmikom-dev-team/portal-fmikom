<?php

namespace App\Actions\Pagi;

use App\Models\User;
use App\Models\PagiWork;
use App\Notifications\PagiNotification;

class LikeWorkAction
{
    /**
     * Execute toggling likes on a work project.
     */
    public function execute(User $authUser, int $previewId): array
    {
        $portfolio = PagiWork::findOrFail($previewId);

        $likes = $portfolio->likes ?? [];
        $isNowLiked = !in_array($authUser->id, $likes);

        if ($isNowLiked) {
            $likes[] = $authUser->id;
        } else {
            $likes = array_values(array_filter($likes, fn($id) => $id !== $authUser->id));
        }

        $portfolio->update(['likes' => $likes]);

        // Send real-time notification to the owner if liked & is not own project
        if ($isNowLiked && $portfolio->user_id !== $authUser->id) {
            $owner = $portfolio->user;
            if ($owner) {
                $avatar = $authUser->foto_path
                    ? (str_starts_with($authUser->foto_path, 'http') ? $authUser->foto_path : asset('storage/' . $authUser->foto_path))
                    : null;

                try {
                    $owner->notify(new PagiNotification(
                        type: 'like',
                        title: $authUser->pagi_username ?: $authUser->name,
                        message: 'menyukai postingan Anda: "' . ($portfolio->title ?? 'Untitled Project') . '"',
                        avatar: $avatar,
                        href: '/pagi/profile/' . $portfolio->user_id . '?project=' . $portfolio->id,
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

        return [
            'liked' => $isNowLiked,
            'likes' => count($likes),
        ];
    }
}
