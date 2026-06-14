<?php

namespace App\Modules\Wims\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Wims\Services\Admin\AdminMonitoringPageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MonitoringController extends Controller
{
    public function __construct(
        private readonly AdminMonitoringPageService $adminMonitoringPageService,
    ) {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Modules/Wims/Admin/Monitoring/Index', $this->adminMonitoringPageService->build($request));
    }
}
