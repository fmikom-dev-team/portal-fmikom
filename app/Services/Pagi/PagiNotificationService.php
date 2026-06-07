<?php

namespace App\Services\Pagi;

use App\Models\User;
use Carbon\Carbon;

class PagiNotificationService
{
    /**
     * Get and format user notifications.
     */
    public function getUserNotifications(User $user): array
    {
        $rawNotifs = $user->notifications()->latest()->limit(100)->get()->map(function ($notif) {
            $data = $notif->data;
            return [
                'id'      => $notif->id,
                'type'    => $data['type']    ?? 'system',
                'title'   => $data['title']   ?? 'PAGI System',
                'message' => $data['message'] ?? '',
                'avatar'  => $data['avatar']  ?? null,
                'href'    => $data['href']     ?? '/pagi',
                'unread'  => is_null($notif->read_at),
                'time'    => $notif->created_at->diffForHumans(),
                'created_at' => $notif->created_at->toISOString(),
                'sender_id' => $data['sender_id'] ?? null,
                'portfolio_id' => $data['portfolio_id'] ?? null,
            ];
        });

        $now = now();
        $todayStart = $now->copy()->startOfDay();
        $weekStart = $now->copy()->subDays(7)->startOfDay();
        $monthStart = $now->copy()->subDays(30)->startOfDay();

        $groups = [];

        $today = $rawNotifs->filter(fn ($n) => Carbon::parse($n['created_at'])->gte($todayStart));
        $week = $rawNotifs->filter(fn ($n) => Carbon::parse($n['created_at'])->lt($todayStart) && Carbon::parse($n['created_at'])->gte($weekStart));
        $month = $rawNotifs->filter(fn ($n) => Carbon::parse($n['created_at'])->lt($weekStart) && Carbon::parse($n['created_at'])->gte($monthStart));
        $older = $rawNotifs->filter(fn ($n) => Carbon::parse($n['created_at'])->lt($monthStart));

        if ($today->isNotEmpty())  $groups[] = ['group' => 'Hari Ini',  'items' => $today->values()];
        if ($week->isNotEmpty())   $groups[] = ['group' => 'Minggu Ini', 'items' => $week->values()];
        if ($month->isNotEmpty())  $groups[] = ['group' => 'Bulan Ini', 'items' => $month->values()];
        if ($older->isNotEmpty())  $groups[] = ['group' => 'Sebelumnya', 'items' => $older->values()];

        return [
            'groups' => $groups,
            'unreadCount' => $user->unreadNotifications()->count(),
        ];
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(User $user, string $id): void
    {
        $user->notifications()->findOrFail($id)->markAsRead();
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }

    /**
     * Delete a single notification.
     */
    public function delete(User $user, string $id): void
    {
        $user->notifications()->findOrFail($id)->delete();
    }

    /**
     * Delete all notifications.
     */
    public function clearAll(User $user): void
    {
        $user->notifications()->delete();
    }
}
