<?php

namespace App\Http\Middleware;

use App\Http\Resources\UserResource;
use App\Models\Pagi\PagiMessage;
use App\Models\Portal\PortalComment;
use App\Models\Portal\PortalMenu;
use App\Models\Portal\PortalPost;
use App\Models\Portal\PortalSetting;
use App\Models\Surat;
use App\Modules\Fast\Services\Shared\NotificationFeedService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
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
        $activeModule = strtoupper((string) $request->attributes->get('resolved_module', session('active_module', '')));
        $routeRole = strtolower((string) ($request->segment(1) ?? ''));
        $activeRole = strtolower((string) $request->attributes->get(
            'resolved_role',
            session('active_role', $routeRole ?: ($user?->userTypeSlug() ?? '')),
        ));

        if ($activeModule !== 'FAST' && in_array($routeRole, ['admin', 'kaprodi', 'dekan', 'mahasiswa', 'dosen'], true)) {
            $activeModule = 'FAST';
        }

        // Auto-prune notifications older than 3 months (run ~1% of requests to keep performance)
        if ($user && rand(1, 100) === 1) {
            $user->notifications()->where('created_at', '<', now()->subMonths(3))->delete();
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'reverb' => [
                'key' => config('broadcasting.connections.reverb.key'),
                'host' => config('broadcasting.vite_reverb.host') ?: config('broadcasting.connections.reverb.options.host'),
                'port' => config('broadcasting.vite_reverb.port') ?: config('broadcasting.connections.reverb.options.port'),
                'scheme' => config('broadcasting.vite_reverb.scheme') ?: config('broadcasting.connections.reverb.options.scheme'),
            ],
            'siteSettings' => Cache::rememberForever('portal_settings', function () {
                $raw = PortalSetting::pluck('value', 'key')->toArray();
                $raw['brand_name'] = $raw['brand_name'] ?? 'Portal FMIKOM';
                $raw['brand_subtitle'] = $raw['brand_subtitle'] ?? 'Fakultas Matematika dan Ilmu Komputer';
                $raw['brand_logo'] = $raw['brand_logo'] ?? '/asset/brand-logo.webp';
                $raw['brand_favicon'] = $raw['brand_favicon'] ?? '/asset/favicon.ico';

                return $raw;
            }),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'import_errors' => fn () => $request->session()->get('import_errors'),
            ],
            'auth' => [
                // ⚠️ KEAMANAN: HANYA field yang dibutuhkan UI yang dibagikan ke frontend.
                // Field sensitif (password, two_factor_secret, otp_code, dll) DILARANG di sini.
                'user' => $user ? (new UserResource($user))->resolve() : null,
                'session_lifetime' => (int) config('session.lifetime') * 60 * 1000,
            ],
            'unread_messages_count' => $user
                // BUG-013: Cache per-user unread count — was firing DB query on every Inertia request.
                // 30-second TTL is short enough for near-real-time feel, eliminates 90% of queries.
                ? Cache::remember("unread_msg_count_{$user->id}", 30, fn () => PagiMessage::where('receiver_id', $user->id)->whereNull('read_at')->count()
                )
                : 0,
            'unread_notifications_count' => $user
                ? Cache::remember("unread_notif_count_{$user->id}_{$activeRole}", 30, function () use ($user, $activeModule, $activeRole) {
                    $query = $user->unreadNotifications();

                    if ($activeModule === 'PAGI' && $activeRole !== 'mahasiswa') {
                        $query->whereNotIn('data->type', ['like', 'comment', 'follow', 'collaboration']);
                    }

                    return $query->count();
                })
                : 0,
            'recent_notifications' => $user ? fn () => Cache::remember("recent_notifs_{$user->id}_{$activeRole}", 30, function () use ($user, $activeModule, $activeRole) {
                $query = $user->notifications()->latest();

                if ($activeModule === 'PAGI' && $activeRole !== 'mahasiswa') {
                    $query->whereNotIn('data->type', ['like', 'comment', 'follow', 'collaboration']);
                }

                $notifs = $query->limit(30)->get();

                return $notifs->map(fn ($n) => [
                    'id' => $n->id,
                    'type' => $n->data['type'] ?? 'system',
                    'title' => $n->data['title'] ?? 'PAGI System',
                    'message' => $n->data['message'] ?? '',
                    'avatar' => $n->data['avatar'] ?? null,
                    'href' => $n->data['href'] ?? '/pagi',
                    'unread' => is_null($n->read_at),
                    'time' => $n->created_at->diffForHumans(),
                    'created_at' => $n->created_at->toISOString(),
                    'sender_id' => $n->data['sender_id'] ?? null,
                    'portfolio_id' => $n->data['portfolio_id'] ?? null,
                ])->values()->toArray();
            }) : [],
            'notifications' => $user ? fn () => $this->fastNotifications($request, $user) : null,

            // Bagikan active context ke semua Vue component via usePage().props.context
            // Digunakan untuk menampilkan badge modul/role aktif di navbar, sidebar, dll.
            'context' => $user ? [
                'active_module' => $activeModule,
                'active_role' => $activeRole,
            ] : null,
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'pending_comments_count' => fn () => ($user && ($user->isAdmin() || $user->isSuperAdmin()))
                // BUG-013: Cache admin-only pending comments count
                ? Cache::remember('pending_comments_count', 30, fn () => PortalComment::where('status', 'pending')->count())
                : 0,
            'notif_count_pending_admin' => $user ? $this->fastPendingAdminCount() : 0,
            'notif_count_revision_admin' => $user ? $this->fastRevisionAdminCount() : 0,
            'nav_counts' => $user ? $this->fastNavCounts($request) : [
                'admin_queue' => 0,
                'approval_queue' => 0,
            ],
            'portal_menus' => Inertia::defer(fn () => Cache::rememberForever('portal_menus', function () {
                return PortalMenu::with(['children.page', 'page'])
                    ->whereNull('parent_id')
                    ->orderBy('order')
                    ->get();
            }))->once(),
            // 3 artikel terbaru untuk preview di mega menu "Berita & Media"
            'featured_posts' => Inertia::defer(fn () => Cache::remember('portal_featured_posts', 3600, function () {
                return PortalPost::where('is_published', true)
                    ->select('id', 'title', 'slug', 'excerpt', 'thumbnail', 'published_at', 'created_at')
                    ->latest('published_at')
                    ->limit(1)
                    ->get()
                    ->map(fn ($p) => [
                        'title' => $p->title,
                        'slug' => $p->slug,
                        'excerpt' => $p->excerpt,
                        'thumbnail' => $p->thumbnail,
                        'published_at' => $p->published_at
                            ? Carbon::parse($p->published_at)->translatedFormat('d M Y')
                            : Carbon::parse($p->created_at)->translatedFormat('d M Y'),
                    ])
                    ->toArray();
            }))->once(),
        ];
    }

    protected function fastPendingAdminCount(): int
    {
        return Cache::remember('notif_count_pending_admin', 30, fn () => Surat::query()
            ->where('type', 'pengajuan')
            ->where('status', Surat::STATUS_PENDING)
            ->count());
    }

    protected function fastRevisionAdminCount(): int
    {
        return Cache::remember('notif_count_revision_admin', 30, fn () => Surat::query()
            ->where('type', 'surat_keluar')
            ->where('status', Surat::STATUS_REVISION_REQUESTED)
            ->count());
    }

    /**
     * @return array{count: int, items: array<int, array<string, mixed>>}|null
     */
    protected function fastNotifications(Request $request, $user): ?array
    {
        $activeModule = strtoupper((string) $request->attributes->get('resolved_module', session('active_module', '')));
        $routeRole = strtolower((string) ($request->segment(1) ?? ''));

        if ($activeModule !== 'FAST' && in_array($routeRole, ['admin', 'kaprodi', 'dekan', 'mahasiswa', 'dosen'], true)) {
            $activeModule = 'FAST';
        }

        if ($activeModule !== 'FAST') {
            return null;
        }

        $activeRole = strtolower((string) $request->attributes->get('resolved_role', session('active_role', $routeRole ?: ($user->userTypeSlug() ?? ''))));

        return Cache::remember(
            "fast_notifications_{$user->id}_{$activeRole}",
            30,
            fn () => app(NotificationFeedService::class)->build($user, $activeRole),
        );
    }

    /**
     * @return array{admin_queue: int, approval_queue: int}
     */
    protected function fastNavCounts(Request $request): array
    {
        $activeModule = strtoupper((string) session('active_module', ''));
        $routeRole = strtolower((string) ($request->segment(1) ?? ''));
        $activeRole = strtolower((string) $request->attributes->get('resolved_role', session('active_role', $routeRole)));

        if ($activeModule !== 'FAST' && in_array($routeRole, ['dekan', 'kaprodi'], true)) {
            $activeModule = 'FAST';
        }

        if ($activeModule !== 'FAST') {
            return [
                'admin_queue' => 0,
                'approval_queue' => 0,
            ];
        }

        $approvalQueueCount = in_array($activeRole, ['kaprodi', 'dekan'], true)
            ? Surat::query()
                ->where('status', Surat::STATUS_VALIDATED_ADMIN)
                ->whereHas('jenisSurat.approvalRole', function ($roleQuery) use ($activeRole): void {
                    $roleQuery
                        ->where('slug', 'like', "%{$activeRole}%")
                        ->orWhere('nama', 'like', "%{$activeRole}%");
                })
                ->count()
            : 0;

        return [
            'admin_queue' => $this->fastPendingAdminCount(),
            'approval_queue' => $approvalQueueCount,
        ];
    }
}
