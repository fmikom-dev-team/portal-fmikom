<?php

namespace App\Http\Middleware;

use App\Models\Surat;
use App\Modules\Fast\Services\Shared\NotificationFeedService;
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
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

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
        $notifications = $user ? app(NotificationFeedService::class)->build($user) : ['count' => 0, 'items' => []];
        $roleSlug = $user?->userTypeSlug() ?? '';

        $navCounts = [
            'admin_queue' => 0,
            'approval_queue' => 0,
        ];

        if ($user) {
            if ($roleSlug === 'admin') {
                $navCounts['admin_queue'] = \App\Models\Surat::query()
                    ->where('type', 'pengajuan')
                    ->where('status', \App\Models\Surat::STATUS_PENDING)
                    ->count();
            }

            if (in_array($roleSlug, ['kaprodi', 'dekan'], true)) {
                $navCounts['approval_queue'] = \App\Models\Surat::query()
                    ->where('status', \App\Models\Surat::STATUS_VALIDATED_ADMIN)
                    ->whereHas('jenisSurat.approvalRole', function ($q) use ($roleSlug): void {
                        $q->where(function ($nested) use ($roleSlug): void {
                            $nested->where('slug', 'like', "%{$roleSlug}%")
                                ->orWhere('nama', 'like', "%{$roleSlug}%");
                        });
                    })
                    ->count();
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'notifications' => $notifications,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'notif_count' => $notifications['count'] ?? 0,
            'nav_counts' => $navCounts,
            'notif_count_revision_admin' => function () use ($user) {
                if (! $user) {
                    return 0;
                }

                $roleSlug = $user->userTypeSlug();

                if (! in_array($roleSlug, ['admin'], true)) {
                    return 0;
                }

                return \App\Models\Surat::query()
                    ->where('type', 'surat_keluar')
                    ->where('status', \App\Models\Surat::STATUS_REVISION_REQUESTED)
                    ->count();
            },
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
