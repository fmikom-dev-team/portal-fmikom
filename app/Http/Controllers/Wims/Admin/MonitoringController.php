<?php

namespace App\Http\Controllers\Wims\Admin;

use App\Http\Controllers\Controller;
use App\Services\Wims\Admin\AdminMonitoringPageService;
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
        return Inertia::render('Wims/Admin/Monitoring/Index', $this->adminMonitoringPageService->build($request));
    }
}
