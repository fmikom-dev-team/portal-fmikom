<?php

namespace App\Modules\Trace\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Trace\Controllers\Alumni\TraceAlumniDashboardController;
use Inertia\Inertia;

class TraceDashboardController extends Controller
{
    private const ADMIN_ROLES = ['super-admin', 'admin', 'admin-universitas', 'admin-akademik', 'prodi'];

    public function index(Request $request)
    {
        $role = $request->attributes->get('resolved_role', session('active_role'));
        $isAdmin = in_array($role, self::ADMIN_ROLES);

        if ($isAdmin) {
            $componentName = 'Modules/Trace/Admin/Dashboard';

            return Inertia::render($componentName, [
                'moduleName' => 'TRACE',
                'roleName' => $role,
            ]);
        }

        // Mitra → redirect to mitra dashboard
        if ($role === 'mitra') {
            return redirect()->route('module.trace.open-job.mitra-dashboard');
        }

        // Alumni (default)
        return app(TraceAlumniDashboardController::class)->index($request);
    }
}
