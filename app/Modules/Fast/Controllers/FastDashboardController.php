<?php

namespace App\Modules\Fast\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FastDashboardController extends Controller
{
    private const ADMIN_ROLES = ['super-admin', 'admin', 'admin-universitas', 'admin-akademik', 'prodi'];

    public function index(Request $request)
    {
        $role = $request->attributes->get('resolved_role', session('active_role'));
        $normalizedRole = strtolower((string) $role);

        if ($normalizedRole === 'kaprodi' || $normalizedRole === 'dekan') {
            return redirect()->route($normalizedRole === 'kaprodi' ? 'kaprodi.dashboard' : 'dekan.dashboard');
        }

        $componentName = match (true) {
            in_array($normalizedRole, self::ADMIN_ROLES, true) => 'Modules/Fast/Admin/Dashboard',
            $normalizedRole === 'dosen' => 'Modules/Fast/Dosen/Dashboard',
            $normalizedRole === 'mahasiswa' => 'Modules/Fast/Mahasiswa/Dashboard',
            default => 'Modules/Fast/Mahasiswa/Dashboard',
        };

        if (! file_exists(resource_path("js/pages/{$componentName}.vue"))) {
            abort(404, "Dashboard Template untuk Role '{$role}' belum tersedia di {$componentName}.vue");
        }

        return Inertia::render($componentName, [
            'moduleName' => 'FAST',
            'roleName' => $role,
        ]);
    }
}
