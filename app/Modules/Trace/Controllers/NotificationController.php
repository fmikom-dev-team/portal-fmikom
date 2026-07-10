<?php

namespace App\Modules\Trace\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()
            ->notifications()
            ->where('data->href', 'like', '/trace%')
            ->latest();

        if ($request->input('filter') === 'unread') {
            $query->whereNull('read_at');
        }

        $notifications = $query->paginate(20)->withQueryString();

        $notifications->getCollection()->transform(function ($n) {
            return [
                'id' => $n->id,
                'type' => $n->data['type'] ?? 'system',
                'title' => $n->data['title'] ?? 'Notifikasi',
                'message' => $n->data['message'] ?? '',
                'href' => $n->data['href'] ?? '/trace',
                'unread' => is_null($n->read_at),
                'time' => $n->created_at->diffForHumans(),
                'created_at' => $n->created_at->toISOString(),
                'date' => $n->created_at->format('d M Y'),
            ];
        });

        // Detect user role for layout
        $role = session('active_role', 'alumni');

        return Inertia::render('Modules/Trace/Notifications/Index', [
            'notifications' => $notifications,
            'filter' => $request->input('filter', 'all'),
            'role' => $role,
        ]);
    }

    public function markRead(Request $request, $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        $this->forgetNotificationCaches($request);

        if ($request->wantsJson()) {
            return response()->json(['read' => true]);
        }

        return back();
    }

    public function markAllRead(Request $request)
    {
        $request->user()
            ->unreadNotifications()
            ->where('data->href', 'like', '/trace%')
            ->update(['read_at' => now()]);

        $this->forgetNotificationCaches($request);

        if ($request->wantsJson()) {
            return response()->json(['read' => true]);
        }

        return back();
    }

    private function forgetNotificationCaches(Request $request): void
    {
        $user = $request->user();
        $activeModule = session('active_module', '');
        $activeRole = session('active_role', '');

        Cache::forget("unread_notif_count_{$user->id}_{$activeModule}_{$activeRole}");
        Cache::forget("recent_notifs_{$user->id}_{$activeModule}_{$activeRole}");
    }
}
