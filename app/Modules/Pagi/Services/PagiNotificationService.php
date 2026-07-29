<?php

namespace App\Modules\Pagi\Services;

use App\Models\Pagi\PagiWork;
use App\Models\User;
use Carbon\Carbon;

class PagiNotificationService
{
    /**
     * Get and format user notifications.
     */
    public function getUserNotifications(User $user): array
    {
        $activeRole = strtolower(session('active_role', ''));
        $activeModule = strtoupper(session('active_module', ''));

        $notifs = $user->notifications()->latest()->limit(100)->get();

        if ($activeModule === 'PAGI' && $activeRole !== 'mahasiswa') {
            $studentNotifTypes = ['like', 'comment', 'follow', 'collaboration'];
            $notifs = $notifs->filter(function ($n) use ($studentNotifTypes) {
                $type = $n->data['type'] ?? 'system';

                return ! in_array($type, $studentNotifTypes);
            });
        }

        // Batch resolve portfolio work cover images
        $portfolioIds = $notifs->map(fn ($n) => $n->data['portfolio_id'] ?? $n->data['work_id'] ?? null)->filter()->unique()->values();
        $worksMap = [];
        if ($portfolioIds->isNotEmpty()) {
            $works = PagiWork::query()->whereIn('id', $portfolioIds)->select('id', 'cover_image', 'content')->get();
            foreach ($works as $w) {
                $img = null;
                if ($w->cover_image) {
                    $img = str_starts_with($w->cover_image, 'http') ? $w->cover_image : asset('storage/'.$w->cover_image);
                } elseif (is_array($w->content)) {
                    foreach ($w->content as $b) {
                        if (isset($b['preview']) && is_string($b['preview']) && ! str_starts_with($b['preview'], 'blob:')) {
                            $img = str_starts_with($b['preview'], 'http') ? $b['preview'] : asset('storage/'.$b['preview']);
                            break;
                        }
                        if (isset($b['file_path']) && is_string($b['file_path'])) {
                            $img = asset('storage/'.$b['file_path']);
                            break;
                        }
                    }
                }
                $worksMap[$w->id] = $img;
            }
        }

        $user = Auth::user();

        $rawNotifs = $notifs->map(function ($notif) use ($worksMap, $user) {
            $data = $notif->data;
            $pId = $data['portfolio_id'] ?? $data['work_id'] ?? null;
            $workImage = $data['work_image'] ?? ($pId ? ($worksMap[$pId] ?? null) : null);

            $isHandled = isset($data['collaboration_handled']) ? (bool) $data['collaboration_handled'] : false;
            $collabStatus = $data['collaboration_status'] ?? null;

            // Fallback check if user has already accepted or declined collaboration on the work
            if (! $isHandled && $pId && $user) {
                $work = PagiWork::query()->find($pId);
                if ($work && is_array($work->content)) {
                    foreach ($work->content as $block) {
                        if (isset($block['type']) && $block['type'] === 'featured_details' && isset($block['collaborators']) && is_array($block['collaborators'])) {
                            foreach ($block['collaborators'] as $c) {
                                $cName = is_array($c) ? ($c['name'] ?? '') : (string) $c;
                                $cStatus = is_array($c) ? ($c['status'] ?? 'pending') : 'accepted';
                                $cUserId = is_array($c) ? ($c['user_id'] ?? null) : null;
                                if (($cUserId && $cUserId == $user->id) || ltrim($cName, '@') === $user->pagi_username || $cName === $user->name) {
                                    if ($cStatus === 'accepted') {
                                        $isHandled = true;
                                        $collabStatus = 'accept';
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return [
                'id' => $notif->id,
                'type' => $data['type'] ?? 'system',
                'title' => $data['title'] ?? 'PAGI System',
                'message' => $data['message'] ?? '',
                'avatar' => $data['avatar'] ?? null,
                'href' => $data['href'] ?? '/pagi',
                'unread' => is_null($notif->read_at),
                'time' => $notif->created_at->diffForHumans(),
                'created_at' => $notif->created_at->toISOString(),
                'sender_id' => $data['sender_id'] ?? null,
                'portfolio_id' => $pId,
                'work_image' => $workImage,
                'is_invite' => isset($data['is_invite']) ? (bool) $data['is_invite'] : (! str_contains($data['message'] ?? '', 'menerima') && ! str_contains($data['message'] ?? '', 'ditolak')),
                'collaboration_handled' => $isHandled,
                'collaboration_status' => $collabStatus,
            ];
        });

        $now = now();
        $todayStart = $now->copy()->startOfDay();
        $yesterdayStart = $now->copy()->subDay()->startOfDay();
        $weekStart = $now->copy()->subDays(7)->startOfDay();
        $monthStart = $now->copy()->subDays(30)->startOfDay();

        $groups = [];

        $today = $rawNotifs->filter(fn ($n) => Carbon::parse($n['created_at'])->gte($todayStart));
        $yesterday = $rawNotifs->filter(fn ($n) => Carbon::parse($n['created_at'])->lt($todayStart) && Carbon::parse($n['created_at'])->gte($yesterdayStart));
        $week = $rawNotifs->filter(fn ($n) => Carbon::parse($n['created_at'])->lt($yesterdayStart) && Carbon::parse($n['created_at'])->gte($weekStart));
        $month = $rawNotifs->filter(fn ($n) => Carbon::parse($n['created_at'])->lt($weekStart) && Carbon::parse($n['created_at'])->gte($monthStart));
        $older = $rawNotifs->filter(fn ($n) => Carbon::parse($n['created_at'])->lt($monthStart));

        if ($today->isNotEmpty()) {
            $groups[] = ['group' => 'Hari Ini', 'items' => $today->values()];
        }
        if ($yesterday->isNotEmpty()) {
            $groups[] = ['group' => 'Kemarin', 'items' => $yesterday->values()];
        }
        if ($week->isNotEmpty()) {
            $groups[] = ['group' => '7 Hari Terakhir', 'items' => $week->values()];
        }
        if ($month->isNotEmpty()) {
            $groups[] = ['group' => '30 Hari Terakhir', 'items' => $month->values()];
        }
        if ($older->isNotEmpty()) {
            $groups[] = ['group' => 'Sebelumnya', 'items' => $older->values()];
        }

        $unreadNotifs = $user->unreadNotifications;
        if ($activeModule === 'PAGI' && $activeRole !== 'mahasiswa') {
            $studentNotifTypes = ['like', 'comment', 'follow', 'collaboration'];
            $unreadNotifs = $unreadNotifs->filter(function ($n) use ($studentNotifTypes) {
                $type = $n->data['type'] ?? 'system';

                return ! in_array($type, $studentNotifTypes);
            });
        }

        return [
            'groups' => $groups,
            'unreadCount' => $unreadNotifs->count(),
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
