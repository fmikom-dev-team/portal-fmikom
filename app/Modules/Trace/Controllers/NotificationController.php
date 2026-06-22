<?php

namespace App\Modules\Trace\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->notifications()->latest();

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

        return back();
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}
