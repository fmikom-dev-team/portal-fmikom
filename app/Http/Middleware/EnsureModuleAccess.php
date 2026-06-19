<?php

namespace App\Http\Middleware;

use App\Models\UserModuleRole;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    /**
     * Middleware utama untuk otorisasi module-level.
     *
     * Penggunaan di route:
     *   ->middleware('module.access')                     // validasi saja
     *   ->middleware('module.access:mahasiswa,dosen')     // validasi + filter role
     *
     * Membaca context dari:
     *   1. Route parameter: {moduleCode} dan {roleSlug}     (URL-based context — ideal)
     *   2. Session: active_module dan active_role            (session-based — fallback)
     */
    public function handle(Request $request, Closure $next, string ...$allowedRoles): Response
    {
        $user = Auth::user();

        // 1. Tentukan module dan role dari URL param atau session (fallback)
        $moduleCode = strtoupper($request->route('moduleCode') ?? session('active_module', ''));
        $roleSlug = $request->route('roleSlug') ?? session('active_role', '');

        if (! $moduleCode || ! $roleSlug) {
            if ($request->inertia()) {
                return response()->json(['message' => 'Konteks modul tidak ditemukan.'], 422);
            }

            return redirect()->route('dashboard')
                ->with('error', 'Silakan pilih modul dan role terlebih dahulu.');
        }

        // 2. Validasi assignment ke DB dengan cache 5 menit (mencegah DB hit setiap request)
        $assignment = $this->getAssignment($user->id, $moduleCode, $roleSlug);

        // 3. Fallback ke user_type jika tidak ada assignment
        if (! $assignment) {
            $userType = $user->getGlobalRoleSlug();

            if ($roleSlug !== $userType) {
                // Hapus session yang stale (mungkin assignment dicabut admin)
                if (session('active_role') === $roleSlug) {
                    session()->forget(['active_module', 'active_role', 'active_module_at']);
                }
                abort(403, "Akses ditolak: assignment tidak ditemukan untuk role '{$roleSlug}' di modul '{$moduleCode}'.");
            }
            // Lanjut dengan user_type sebagai fallback identity (tanpa assignment record)
        }

        // 4. Cek apakah assignment sudah expired (jika pakai expires_at)
        if ($assignment && $assignment->expires_at && $assignment->expires_at->isPast()) {
            Cache::forget("module_access_{$user->id}_{$moduleCode}_{$roleSlug}");
            session()->forget(['active_module', 'active_role', 'active_module_at']);

            return redirect()->route('dashboard')
                ->with('error', 'Akses role ini telah kedaluwarsa. Silakan pilih ulang.');
        }

        // 5. Cek role spesifik yang diizinkan (dari argumen middleware di route)
        if (! empty($allowedRoles) && ! in_array($roleSlug, $allowedRoles)) {
            abort(403, "Role '{$roleSlug}' tidak memiliki izin di halaman ini.");
        }

        // 6. Inject context ke request agar controller tidak perlu baca session langsung
        $request->attributes->set('resolved_module', $moduleCode);
        $request->attributes->set('resolved_role', $roleSlug);

        return $next($request);
    }

    /**
     * Ambil record assignment dari cache atau DB.
     * Cache key per user + modul + role — invalidate manual saat ganti role.
     */
    private function getAssignment(int $userId, string $moduleCode, string $roleSlug): ?UserModuleRole
    {
        return Cache::remember(
            "module_access_{$userId}_{$moduleCode}_{$roleSlug}",
            now()->addMinutes(5),
            fn () => UserModuleRole::where('user_id', $userId)
                ->where('is_active', true)
                ->whereHas('module', fn ($q) => $q->where('code', $moduleCode)->where('is_active', true))
                ->whereHas('role', fn ($q) => $q->where('slug', $roleSlug))
                ->with(['module', 'role'])
                ->first()
        );
    }
}
