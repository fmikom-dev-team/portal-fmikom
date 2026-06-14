<?php

namespace App\Modules\Wims\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Wims\Services\Dosen\DosenDashboardPageService;
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

        return Inertia::render('Modules/Wims/Dosen/Dashboard', $this->dosenDashboardPageService->build($user));
    }
}
