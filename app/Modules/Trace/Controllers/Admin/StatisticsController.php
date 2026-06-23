<?php

namespace App\Modules\Trace\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Trace\Services\TraceStatisticsService;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class StatisticsController extends Controller
{
    public function __construct(private TraceStatisticsService $statisticsService) {}

    public function index(): InertiaResponse
    {
        return Inertia::render(
            'Modules/Trace/Admin/Statistics',
            $this->statisticsService->getDashboardData()
        );
    }
}
