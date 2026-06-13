<?php

namespace App\Http\Controllers\Wims\Admin;

use App\Http\Controllers\Controller;
use App\Services\Wims\Admin\AdminDashboardPageService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardPageService $adminDashboardPageService,
    ) {
    }

    public function index(): Response
    {
        return Inertia::render('Wims/Admin/Dashboard', $this->adminDashboardPageService->build());
    }
}
