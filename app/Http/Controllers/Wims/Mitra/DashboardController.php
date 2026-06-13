<?php

namespace App\Http\Controllers\Wims\Mitra;

use App\Http\Controllers\Controller;
use App\Services\Wims\Mitra\MitraDashboardPageService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly MitraDashboardPageService $mitraDashboardPageService,
    ) {
    }

    public function index(): Response
    {
        return Inertia::render('Wims/Mitra/Dashboard', $this->mitraDashboardPageService->build(auth()->user()));
    }
}
