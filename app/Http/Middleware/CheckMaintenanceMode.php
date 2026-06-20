<?php

namespace App\Http\Middleware;

use App\Models\Portal\PortalSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $settings = Cache::rememberForever('portal_settings', function () {
            return PortalSetting::pluck('value', 'key')->toArray();
        });

        $maintenanceMode = $settings['maintenance_mode'] ?? '0';

        if ($maintenanceMode === '1') {
            // Check if user is super-admin (using standard helper)
            if (auth()->check() && auth()->user()->isSuperAdmin()) {
                return $next($request);
            }

            // Exclude admin routes, login/logout, and internal API endpoints
            $excludedPatterns = [
                'workos*',
                'portal-admin*',
                'login',
                'logout',
                'two-factor-challenge*',
                'auth*',
                'verify-otp',
                'resend-otp',
                'force-change-password',
                'api/check-user-exists',
                'horizon*',
                'broadcasting/*',
                '_debugbar*',
            ];

            foreach ($excludedPatterns as $pattern) {
                if ($request->is($pattern)) {
                    return $next($request);
                }
            }

            // Render modern public maintenance page
            $message = $settings['maintenance_message'] ?? 'Sistem sedang dalam pemeliharaan. Silakan kembali beberapa saat lagi.';

            if ($request->header('X-Inertia')) {
                return Inertia::render('Public/Maintenance', [
                    'message' => $message,
                ]);
            }

            $response = Inertia::render('Public/Maintenance', ['message' => $message])->toResponse($request);
            $response->setStatusCode(503);

            return $response;
        }

        return $next($request);
    }
}
