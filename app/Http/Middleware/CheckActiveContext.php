<?php

namespace App\Http\Middleware;

use App\Models\UserModuleRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveContext
{
    /**
     * Middleware untuk validasi State/Session Active Module.
     * Memvalidasi ke DB (dengan cache 5 menit) — bukan hanya percaya session.
     *
     * Contoh penggunaan di routes:
     *   ->middleware('module.context:WIMS')
     *   ->middleware('module.context:WIMS,super-admin,admin')
     */
    public function handle(Request $request, Closure $next, string $moduleCode, string ...$allowedRoles): Response
    {
        $user = Auth::user();

        // 1. Cek apakah session module aktif sesuai modul yang dideklarasi di route
        $activeModule = session('active_module');
        $activeRole = session('active_role');

        if (! $activeModule || strtoupper($activeModule) !== strtoupper($moduleCode)) {
            return redirect()->route('dashboard')
                ->with('error', 'Akses Ilegal: Silakan pilih modul terlebih dahulu.');
        }

        if (! $activeRole) {
            return redirect()->route('dashboard')
                ->with('error', 'Role aktif tidak ditemukan. Silakan pilih modul kembali.');
        }

        // 2. Re-validasi ke DB dengan cache 5 menit
        //    Ini memastikan jika admin mencabut akses, user akan ditendang maksimal 5 menit kemudian
        $cacheKey = "module_access_{$user->id}_{$activeModule}_{$activeRole}";
        $isValid = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user, $activeModule, $activeRole) {
            $hasAssignment = UserModuleRole::where('user_id', $user->id)
                ->where('is_active', true)
                ->whereHas('module', fn ($q) => $q->where('code', strtoupper($activeModule))->where('is_active', true))
                ->whereHas('role', fn ($q) => $q->where('slug', $activeRole))
                ->exists();

            if ($hasAssignment) {
                return true;
            }

            // Fallback: izinkan jika roleSlug cocok dengan user_type
            $userType = $user->user_type ?? optional($user->role)->slug;

            return $activeRole === $userType;
        });

        if (! $isValid) {
            // Hapus session stale dan invalidate cache
            session()->forget(['active_module', 'active_role', 'active_module_at']);
            Cache::forget($cacheKey);

            return redirect()->route('dashboard')
                ->with('error', 'Akses Anda ke modul ini telah dicabut atau tidak valid.');
        }

        // 3. Cek role spesifik yang diizinkan (jika argumen diberikan di route)
        if (! empty($allowedRoles) && ! in_array($activeRole, $allowedRoles)) {
            abort(403, 'Akses Ditolak: Role Anda ('.$activeRole.') tidak diizinkan di halaman ini.');
        }

        // 4. Inject ke request agar controller tidak perlu baca session langsung
        $request->attributes->set('resolved_module', strtoupper($activeModule));
        $request->attributes->set('resolved_role', $activeRole);

        return $next($request);
    }
}
