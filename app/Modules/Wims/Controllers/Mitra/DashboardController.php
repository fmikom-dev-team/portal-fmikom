<?php

namespace App\Modules\Wims\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Modules\Wims\Services\Mitra\MitraDashboardPageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly MitraDashboardPageService $mitraDashboardPageService,
    ) {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Modules/Wims/Mitra/Dashboard', $this->mitraDashboardPageService->build($request->user()));
    }
}
