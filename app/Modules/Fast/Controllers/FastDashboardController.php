<?php

namespace App\Modules\Fast\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FastDashboardController extends Controller
{
    private const ADMIN_ROLES = ['super-admin', 'admin', 'admin-universitas', 'admin-akademik', 'prodi'];

    public function index(Request $request)
    {
        $role = $request->attributes->get('resolved_role', session('active_role'));
        $isAdmin = in_array($role, self::ADMIN_ROLES);

        $componentName = $isAdmin
            ? 'Modules/Fast/Admin/Dashboard'
            : 'Modules/Fast/User/'.Str::studly($role).'Dashboard';

        $path = resource_path("js/pages/{$componentName}.vue");
        if (! file_exists($path)) {
            $fallbackName = 'Modules/Fast/User/MahasiswaDashboard';
            if (file_exists(resource_path("js/pages/{$fallbackName}.vue"))) {
                $componentName = $fallbackName;
            } else {
                abort(404, "Dashboard Template untuk Role '{$role}' belum dibuat di {$componentName}.vue");
            }
        }

        return Inertia::render($componentName, [
            'moduleName' => 'FAST',
            'roleName' => $role,
        ]);
    }
}
