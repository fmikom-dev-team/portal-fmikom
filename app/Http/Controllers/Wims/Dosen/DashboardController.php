<?php

namespace App\Http\Controllers\Wims\Dosen;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Wims\Dosen\DosenDashboardPageService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DosenDashboardPageService $dosenDashboardPageService,
    ) {
    }

    public function index(): Response
    {
        /** @var User $user */
        $user = request()->user();

        return Inertia::render('Wims/Dosen/Dashboard', $this->dosenDashboardPageService->build($user));
    }
}
