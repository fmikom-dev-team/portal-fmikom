<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTraceAlumniPreviewIsReadOnly
{
    private const ADMIN_GLOBAL_ROLES = [
        'super-admin',
        'admin',
        'admin-universitas',
        'admin-akademik',
        'prodi',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $activeRole = (string) $request->attributes->get('resolved_role', session('active_role', ''));
        $globalRole = (string) ($user?->getGlobalRoleSlug() ?? '');

        $isReadOnlyPreview = $activeRole === 'alumni' && in_array($globalRole, self::ADMIN_GLOBAL_ROLES, true);

        $request->attributes->set('trace_alumni_read_only', $isReadOnlyPreview);

        if ($isReadOnlyPreview && ! $request->isMethodSafe()) {
            abort(403, 'Mode alumni untuk admin hanya dapat melihat data.');
        }

        return $next($request);
    }
}
