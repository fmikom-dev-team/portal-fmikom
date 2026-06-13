<?php

namespace App\Modules\Wims\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Magang\PendaftaranMagang;
use App\Modules\Wims\Services\Admin\AdminAssessmentRecapExportService;
use App\Modules\Wims\Services\Admin\AdminAssessmentRecapPageService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AssessmentRecapController extends Controller
{
    public function __construct(
        private readonly AdminAssessmentRecapPageService $adminAssessmentRecapPageService,
        private readonly AdminAssessmentRecapExportService $adminAssessmentRecapExportService,
    ) {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Wims/Admin/RekapNilai/Index', $this->adminAssessmentRecapPageService->build($request));
    }

    public function download(PendaftaranMagang $pendaftaran, string $role): HttpResponse
    {
        return $this->adminAssessmentRecapExportService->download($pendaftaran, $role);
    }
}
