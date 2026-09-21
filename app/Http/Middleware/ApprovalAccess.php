<?php

namespace App\Http\Middleware;

use App\Models\UserModuleRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ApprovalAccess
{
    /**
     * Gate FAST approval pages for kaprodi/dekan.
     *
     * Keeps the access flow compatible with the portal module selector,
     * while still allowing direct access when the assignment exists.
     */
    public function handle(Request $request, Closure $next, ?string $expectedRole = null): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect('/login');
        }

        $allowedRoles = ['kaprodi', 'dekan'];
        $expectedRole = $this->normalizeExpectedRole($expectedRole, $allowedRoles);

        $resolvedRole = $this->resolveAllowedRole($user, $allowedRoles, $expectedRole);

        if (! $resolvedRole) {
            return redirect()->route('dashboard')
                ->with('error', 'Akses FAST approval tidak tersedia. Silakan pilih modul yang sesuai.');
        }

        $this->persistContext($resolvedRole);
        $request->attributes->set('resolved_module', 'FAST');
        $request->attributes->set('resolved_role', $resolvedRole);

        return $next($request);
    }

    /**
     * @param  array<int, string>  $allowedRoles
     */
    protected function resolveAllowedRole($user, array $allowedRoles, ?string $expectedRole = null): ?string
    {
        $sessionModule = strtoupper((string) session('active_module', ''));
        $sessionRole = strtolower((string) session('active_role', ''));

        if ($sessionModule === 'FAST' && in_array($sessionRole, $allowedRoles, true)) {
            if ($expectedRole !== null && $sessionRole !== $expectedRole) {
                return null;
            }

            $cacheKey = "module_access_{$user->id}_{$sessionModule}_{$sessionRole}";
            $isValid = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user, $sessionModule, $sessionRole): bool {
                return UserModuleRole::query()
                    ->where('user_id', $user->id)
                    ->where('is_active', true)
                    ->whereHas('module', fn ($query) => $query
                        ->where('code', $sessionModule)
                        ->where('is_active', true))
                    ->whereHas('role', fn ($query) => $query->where('slug', $sessionRole))
                    ->exists();
            });

            if ($isValid) {
                return $sessionRole;
            }

            Cache::forget($cacheKey);
            session()->forget(['active_module', 'active_role', 'active_module_at']);
        }

        $assignedRoles = UserModuleRole::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereHas('module', fn ($query) => $query->where('code', 'FAST')->where('is_active', true))
            ->whereHas('role', fn ($query) => $expectedRole !== null
                ? $query->where('slug', $expectedRole)
                : $query->whereIn('slug', $allowedRoles))
            ->with('role')
            ->get();

        if ($assignedRoles->count() === 1) {
            return $assignedRoles->first()?->role?->slug;
        }

        return null;
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
