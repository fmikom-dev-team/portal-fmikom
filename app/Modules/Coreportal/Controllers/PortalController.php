<?php

namespace App\Modules\Coreportal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserModuleRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class PortalController extends Controller
{
    /**
     * Menampilkan daftar modul yang bisa diakses user (Dashboard SSO Portal)
     */
    public function index()
    {
        $user = Auth::user();

        // Ambil semua hak akses modul dan role yang dimilikinya (eager load untuk hindari N+1)
        // Hanya tampilkan jika role tersebut SUDAH di-mapping ke modul via module_roles (pivot)
        $userModules = UserModuleRole::with(['module', 'role'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereHas('module', fn ($q) => $q->where('is_active', true))
            ->whereHas('role', function ($q) {
                // Role harus terdaftar di tabel module_roles untuk modul ini
                $q->whereExists(function ($sub) {
                    $sub->from('module_roles')
                        ->whereColumn('module_roles.role_id', 'roles.id')
                        ->whereColumn('module_roles.module_id', 'user_module_roles.module_id');
                });
            })
            ->get();

        return Inertia::render('Dashboard', [
            'accessList' => $userModules,
        ]);
    }

    /**
     * Memproses logika saat user memilih modul dan memvalidasinya.
     * Mencegah IDOR: validasi assignment ke DB sebelum set session.
     */
    public function selectModule(Request $request)
    {
        $request->validate([
            'module_code' => ['required', 'string', 'max:50'],
            'role_slug' => ['required', 'string', 'max:100'],
        ]);

        $user = Auth::user();
        $moduleCode = strtoupper($request->module_code);
        $roleSlug = $request->role_slug;

        // Validasi akses ke modul DAN ROLE spesifik ini (mencegah IDOR / manipulasi data)
        $access = UserModuleRole::with(['role', 'module'])
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereHas('module', fn ($q) => $q->where('code', $moduleCode)->where('is_active', true))
            ->whereHas('role', function ($q) use ($roleSlug, $moduleCode) {
                $q->where('slug', $roleSlug)
                    ->whereExists(function ($sub) use ($moduleCode) {
                        $sub->from('module_roles')
                            ->whereColumn('module_roles.role_id', 'roles.id')
                            ->whereExists(function ($mod) use ($moduleCode) {
                                $mod->from('modules')
                                    ->whereColumn('modules.id', 'module_roles.module_id')
                                    ->where('modules.code', $moduleCode);
                            });
                    });
            })
            ->first();

        if (! $access) {
            abort(403, 'Akses Ditolak: Role ini tidak tersedia untuk organisasi/modul tersebut.');
        }

        $finalModuleCode = $access ? $access->module->code : $moduleCode;
        $finalRoleSlug = $access ? $access->role->slug : $roleSlug;

        // Invalidate cache akses lama sebelum set session baru
        $oldRole = session('active_role');
        if ($oldRole && $oldRole !== $finalRoleSlug) {
            Cache::forget("module_access_{$user->id}_{$finalModuleCode}_{$oldRole}");
        }

        // --- INTI OTORISASI: SIMPAN STATE AKTIF KE SESSION ---
        session([
            'active_module' => $finalModuleCode,
            'active_role' => $finalRoleSlug,
            'active_module_at' => now()->toIso8601String(),
        ]);

        $routeName = 'module.'.strtolower($finalModuleCode).'.dashboard';

        return Route::has($routeName)
            ? redirect()->route($routeName)
            : back()->with('info', "Modul {$finalModuleCode} dipilih, namun halamannya belum tersedia.");
    }

    /**
     * Ganti role dalam modul yang sama tanpa re-pilih modul.
     * Endpoint: POST /portal/switch-role
     *
     * Edge case: user klik "ganti role" saat sudah di dalam modul.
     * Flow: validasi DB → invalidate cache lama → update session → frontend reload.
     */
    public function switchRole(Request $request)
    {
        $request->validate([
            'role_slug' => ['required', 'string', 'max:100'],
        ]);

        $user = Auth::user();
        $moduleCode = session('active_module');
        $newRole = $request->role_slug;
        $oldRole = session('active_role');

        if (! $moduleCode) {
            return response()->json(['message' => 'Tidak ada modul aktif. Silakan pilih modul terlebih dahulu.'], 422);
        }

        if ($newRole === $oldRole) {
            return response()->json(['message' => 'Role yang dipilih sama dengan role aktif saat ini.'], 200);
        }

        // Validasi: user harus punya assignment untuk role baru ini di modul yang sama
        $hasAccess = UserModuleRole::where('user_id', $user->id)
            ->where('is_active', true)
            ->whereHas('module', fn ($q) => $q->where('code', $moduleCode))
            ->whereHas('role', fn ($q) => $q->where('slug', $newRole))
            ->exists();

        if (! $hasAccess) {
            // Fallback: izinkan jika role baru = user_type
            $userType = $user->user_type ?? optional($user->role)->slug;
            if ($newRole !== $userType) {
                return response()->json(['message' => "Role '{$newRole}' tidak valid untuk modul '{$moduleCode}'."], 403);
            }
        }

        // Invalidate cache role lama
        if ($oldRole) {
            Cache::forget("module_access_{$user->id}_{$moduleCode}_{$oldRole}");
        }

        // Update session ke role baru
        session([
            'active_role' => $newRole,
            'active_module_at' => now()->toIso8601String(),
        ]);

        return response()->json([
            'message' => "Role berhasil diganti ke '{$newRole}'.",
            'active_role' => $newRole,
            'active_module' => $moduleCode,
        ]);
    }
}
