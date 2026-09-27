<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserModuleRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
    public function handle(Request $request, Closure $next, ?string $expectedRole = null): Response
    {
        /** @var User|null $user */
        $user = Auth::user();

        if (! $user) {
            return redirect('/login');
        }

        $allowedRoles = ['admin', 'super-admin', 'admin-universitas', 'admin-akademik', 'prodi', 'kaprodi', 'dekan'];
        $expectedRole = $this->normalizeExpectedRole($expectedRole, $allowedRoles);
        $resolvedRole = $this->resolveAllowedRole($user, $allowedRoles, $expectedRole);

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
    protected function resolveAllowedRole(User $user, array $allowedRoles, ?string $expectedRole = null): ?string
    {
        $sessionModule = strtoupper((string) session('active_module', ''));
        $sessionRole = strtolower((string) session('active_role', ''));

        if ($sessionModule === 'FAST' && in_array($sessionRole, $allowedRoles, true)) {
            if ($expectedRole !== null && $sessionRole !== $expectedRole) {
                return null;
            }

            $cacheKey = "module_access_{$user->id}_{$sessionModule}_{$sessionRole}";
            $isValid = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user, $sessionModule, $sessionRole) {
                return UserModuleRole::where('user_id', '=', $user->id, 'and')
                    ->where('is_active', '=', true, 'and')
                    ->whereHas('module', fn ($q) => $q->where('code', '=', $sessionModule, 'and')->where('is_active', '=', true, 'and'))
                    ->whereHas('role', fn ($q) => $q->where('slug', '=', $sessionRole, 'and'))
                    ->exists();
            });

            if ($isValid) {
                return $sessionRole;
            }
        }

        $globalRole = $user->getGlobalRoleSlug();
        if ($expectedRole === null && $globalRole && in_array($globalRole, ['admin', 'super-admin'], true)) {
            return $globalRole;
        }

        $assignedRoles = UserModuleRole::query()
            ->where('user_id', '=', $user->id, 'and')
            ->where('is_active', '=', true, 'and')
            ->whereHas('module', fn ($query) => $query->where('code', '=', 'FAST', 'and')->where('is_active', '=', true, 'and'))
            ->whereHas('role', fn ($query) => $expectedRole !== null
                ? $query->where('slug', '=', $expectedRole, 'and')
                : $query->whereIn('slug', $allowedRoles, 'and', false))
            ->with('role')
            ->get();

        return $assignedRoles->count() === 1
            ? $assignedRoles->first()?->role?->slug
            : null;
    }

    /**
     * @param  array<int, string>  $allowedRoles
     */
    protected function normalizeExpectedRole(?string $expectedRole, array $allowedRoles): ?string
    {
        $expectedRole = strtolower(trim((string) $expectedRole));

        if ($expectedRole === '') {
            return null;
        }

        abort_unless(in_array($expectedRole, $allowedRoles, true), 404);

        return $expectedRole;
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
