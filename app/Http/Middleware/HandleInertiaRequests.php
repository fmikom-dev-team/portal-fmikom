<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        // Auto-prune notifications older than 3 months (run ~1% of requests to keep performance)
        if ($user && rand(1, 100) === 1) {
            $user->notifications()->where('created_at', '<', now()->subMonths(3))->delete();
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
                'import_errors' => fn() => $request->session()->get('import_errors'),
            ],
            'auth' => [
                // ⚠️ KEAMANAN: HANYA field yang dibutuhkan UI yang dibagikan ke frontend.
                // Field sensitif (password, two_factor_secret, otp_code, dll) DILARANG di sini.
                'user' => $user ? (new \App\Http\Resources\UserResource($user))->resolve() : null,
                'session_lifetime' => (int) config('session.lifetime') * 60 * 1000,
            ],
            'unread_messages_count' => $user ? \App\Models\PagiMessage::where('receiver_id', $user->id)->whereNull('read_at')->count() : 0,
            'unread_notifications_count' => $user ? $user->unreadNotifications()->count() : 0,
            'recent_notifications' => $user ? fn() => $user->notifications()->latest()->limit(30)->get()->map(fn($n) => [
                'id'      => $n->id,
                'type'    => $n->data['type']    ?? 'system',
                'title'   => $n->data['title']   ?? 'PAGI System',
                'message' => $n->data['message'] ?? '',
                'avatar'  => $n->data['avatar']  ?? null,
                'href'    => $n->data['href']     ?? '/pagi',
                'unread'  => is_null($n->read_at),
                'time'    => $n->created_at->diffForHumans(),
                'created_at' => $n->created_at->toISOString(),
                'sender_id' => $n->data['sender_id'] ?? null,
                'portfolio_id' => $n->data['portfolio_id'] ?? null,
            ])->values()->toArray() : [],

            // Bagikan active context ke semua Vue component via usePage().props.context
            // Digunakan untuk menampilkan badge modul/role aktif di navbar, sidebar, dll.
            'context' => $user ? [
                'active_module' => session('active_module'),
                'active_role'   => session('active_role'),
            ] : null,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'pending_comments_count' => fn() => ($user && ($user->isAdmin() || $user->isSuperAdmin())) ? \App\Models\PortalComment::where('status', 'pending')->count() : 0,
            'portal_menus' => \Inertia\Inertia::defer(fn() => \Illuminate\Support\Facades\Cache::rememberForever('portal_menus', function () {
                return \App\Models\PortalMenu::with(['children.page', 'page'])
                    ->whereNull('parent_id')
                    ->orderBy('order')
                    ->get();
            }))->once(),
            // 3 artikel terbaru untuk preview di mega menu "Berita & Media"
            'featured_posts' => \Inertia\Inertia::defer(fn() => \Illuminate\Support\Facades\Cache::remember('portal_featured_posts', 3600, function () {
                return \App\Models\PortalPost::where('is_published', true)
                    ->select('id', 'title', 'slug', 'excerpt', 'thumbnail', 'published_at', 'created_at')
                    ->latest('published_at')
                    ->limit(1)
                    ->get()
                    ->map(fn($p) => [
                        'title'        => $p->title,
                        'slug'         => $p->slug,
                        'excerpt'      => $p->excerpt,
                        'thumbnail'    => $p->thumbnail,
                        'published_at' => $p->published_at
                            ? \Carbon\Carbon::parse($p->published_at)->translatedFormat('d M Y')
                            : \Carbon\Carbon::parse($p->created_at)->translatedFormat('d M Y'),
                    ])
                    ->toArray();
            }))->once(),
        ];
    }
}
