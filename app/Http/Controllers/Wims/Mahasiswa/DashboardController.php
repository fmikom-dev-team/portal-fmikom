<?php

namespace App\Http\Controllers\Wims\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Services\Wims\Mahasiswa\Dashboard\StudentDashboardPageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly StudentDashboardPageService $studentDashboardPageService,
    ) {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Wims/Mahasiswa/Dashboard', $this->studentDashboardPageService->build($request->user()));
    }
}
