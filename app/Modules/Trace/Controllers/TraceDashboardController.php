<?php

namespace App\Modules\Trace\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TraceDashboardController extends Controller
{
    private const ADMIN_ROLES = ['super-admin', 'admin', 'admin-universitas', 'admin-akademik', 'prodi'];

    public function index(Request $request)
    {
        $role = $request->attributes->get('resolved_role', session('active_role'));
        $isAdmin = in_array($role, self::ADMIN_ROLES);

        $componentName = $isAdmin 
            ? "Modules/Trace/Admin/Dashboard"
            : "Modules/Trace/User/" . Str::studly($role) . "Dashboard";

        $path = resource_path("js/pages/{$componentName}.vue");
        if (!file_exists($path)) {
            $fallbackName = "Modules/Trace/User/MahasiswaDashboard";
            if (file_exists(resource_path("js/pages/{$fallbackName}.vue"))) {
                $componentName = $fallbackName;
            } else {
                abort(404, "Dashboard Template untuk Role '{$role}' belum dibuat di {$componentName}.vue");
            }
        }

        return Inertia::render($componentName, [
            'moduleName' => 'TRACE',
            'roleName' => $role,
        ]);
    }
}
