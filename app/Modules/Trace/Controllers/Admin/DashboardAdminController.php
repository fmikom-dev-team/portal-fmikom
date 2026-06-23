<?php

namespace App\Modules\Trace\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Trace\Services\DashboardStatsService;
use Inertia\Inertia;

class DashboardAdminController extends Controller
{
    public function index(DashboardStatsService $statsService)
    {
        return Inertia::render('Modules/Trace/Admin/Dashboard', [
            'stats' => Inertia::defer(fn () => $statsService->getAdminStats()),
            'alumniGrowthData' => Inertia::defer(fn () => $statsService->getAlumniGrowthData()),
        ]);
    }
}
