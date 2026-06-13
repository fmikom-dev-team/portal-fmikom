<?php

namespace App\Http\Middleware;

use App\Support\WimsStudentAlerts;
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

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user
                    ? [
                        ...$user->toArray(),
                        'avatar' => $user->photoUrl(),
                        'photo_url' => $user->photoUrl(),
                    ]
                    : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'alerts' => fn () => $this->resolveStudentAlerts($request),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }

    /**
     * @return array<int, array{id: string, message: string}>
     */
    private function resolveStudentAlerts(Request $request): array
    {
        $user = $request->user();

        if (! $user || ! $request->routeIs('wims.*')) {
            return [];
        }

        if (! $user->hasAnyRole(['user', 'super-admin'])) {
            return [];
        }

        return app(WimsStudentAlerts::class)->forUser($user);
    }
}
