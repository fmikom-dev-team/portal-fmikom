<?php

namespace App\Modules\Trace\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Trace\Controllers\Alumni\TraceAlumniDashboardController;
use Inertia\Inertia;

class TraceDashboardController extends Controller
{
    private const ADMIN_ROLES = ['super-admin', 'admin', 'admin-universitas', 'admin-akademik', 'prodi'];

    public function __construct(
        private readonly TraceAlumniDashboardController $alumniDashboard
    ) {}

    public function index(Request $request)
    {
        $role = $request->attributes->get('resolved_role', session('active_role'));
        $isAdmin = in_array($role, self::ADMIN_ROLES);

        if ($isAdmin) {
            return redirect()->route('module.trace.admin.dashboard');
        }

        // Mitra → redirect to mitra dashboard
        if ($role === 'mitra') {
            return redirect()->route('module.trace.open-job.mitra-dashboard');
        }

        // Alumni (default) — delegated via constructor injection
        return $this->alumniDashboard->index($request);
    }
}
