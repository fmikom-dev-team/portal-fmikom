<?php

namespace App\Modules\Trace\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Trace\Controllers\Alumni\TraceAlumniDashboardController;
use Illuminate\Support\Str;
use Inertia\Inertia;

class TraceDashboardController extends Controller
{
    private const ADMIN_ROLES = ['super-admin', 'admin', 'admin-universitas', 'admin-akademik', 'prodi'];

    public function index(Request $request)
    {
        $role = $request->attributes->get('resolved_role', session('active_role'));
        $isAdmin = in_array($role, self::ADMIN_ROLES);

        if (! $isAdmin) {
            return app(TraceAlumniDashboardController::class)->index($request);
        }

        $componentName = 'Modules/Trace/Admin/Dashboard';

        return Inertia::render($componentName, [
            'moduleName' => 'TRACE',
            'roleName' => $role,
        ]);
    }
}
