<?php

namespace App\Modules\Wims\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Wims\Services\Admin\AdminDashboardPageService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardPageService $adminDashboardPageService,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Modules/Wims/Admin/Dashboard', $this->adminDashboardPageService->build());
    }
}
