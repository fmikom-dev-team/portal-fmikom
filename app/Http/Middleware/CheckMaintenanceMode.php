<?php

namespace App\Http\Middleware;

use App\Models\Portal\PortalSetting;
use App\Models\User;
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
            /** @var User|null $user */
            $user = $request->user();
            if ($user && (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin() || method_exists($user, 'isAdmin') && $user->isAdmin())) {
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
            $maintenanceProps = [
                'message' => $message,
                'maintenance' => [
                    'message' => $message,
                    'contact_email' => ! empty($settings['maintenance_contact_email']) ? (string) $settings['maintenance_contact_email'] : null,
                    'contact_wa' => ! empty($settings['maintenance_contact_wa']) ? (string) $settings['maintenance_contact_wa'] : (! empty($settings['helpdesk_wa_number']) ? (string) $settings['helpdesk_wa_number'] : null),
                    'website_url' => ! empty($settings['maintenance_website_url']) ? (string) $settings['maintenance_website_url'] : null,
                    'estimated_end' => ! empty($settings['maintenance_estimated_end']) ? (string) $settings['maintenance_estimated_end'] : null,
                ],
            ];

            if ($request->header('X-Inertia')) {
                return Inertia::render('Public/Maintenance', $maintenanceProps);
            }

            $response = Inertia::render('Public/Maintenance', $maintenanceProps)->toResponse($request);
            $response->setStatusCode(503);

            return $response;
        }

        return $next($request);
    }
}
