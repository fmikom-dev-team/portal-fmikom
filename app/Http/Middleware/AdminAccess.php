<?php

namespace App\Http\Middleware;

use App\Models\UserModuleRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    /**
     * Gate FAST admin pages.
     *
     * This middleware accepts either:
     * - active FAST context from portal selection, or
     * - a direct global admin/super-admin identity.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect('/login');
        }

        $resolvedRole = $this->resolveAllowedRole(
            $user,
            ['admin', 'super-admin', 'admin-universitas', 'admin-akademik', 'prodi', 'kaprodi', 'dekan'],
        );

        if (! $resolvedRole) {
            return redirect()->route('dashboard')
                ->with('error', 'Akses FAST admin tidak tersedia. Silakan pilih modul yang sesuai.');
        }

        $this->persistContext($resolvedRole);
        $request->attributes->set('resolved_module', 'FAST');
        $request->attributes->set('resolved_role', $resolvedRole);

        return $next($request);
    }

    /**
     * @param  array<int, string>  $allowedRoles
     */
    protected function resolveAllowedRole($user, array $allowedRoles): ?string
    {
        $sessionModule = strtoupper((string) session('active_module', ''));
        $sessionRole = strtolower((string) session('active_role', ''));

        if ($sessionModule === 'FAST' && in_array($sessionRole, $allowedRoles, true)) {
            return $sessionRole;
        }

        $globalRole = $user->getGlobalRoleSlug();
        if ($globalRole && in_array($globalRole, $allowedRoles, true)) {
            return $globalRole;
        }

        $assignedRole = UserModuleRole::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereHas('module', fn ($query) => $query->where('code', 'FAST')->where('is_active', true))
            ->whereHas('role', fn ($query) => $query->whereIn('slug', $allowedRoles))
            ->with('role')
            ->first();

        return $assignedRole?->role?->slug;
    }

    protected function persistContext(string $role): void
    {
        session([
            'active_module' => 'FAST',
            'active_role' => $role,
            'active_module_at' => now()->toIso8601String(),
        ]);
    }
}
