<?php

namespace App\Modules\Wims\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Modules\Wims\Services\Mahasiswa\Report\StudentFinalReportActionService;
use App\Modules\Wims\Services\Mahasiswa\Report\StudentFinalReportFileService;
use App\Modules\Wims\Services\Mahasiswa\Report\StudentFinalReportTemplateService;
use App\Modules\Wims\Services\Mahasiswa\Report\StudentReportPageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LaporanController extends Controller
{
    public function __construct(
        private readonly StudentReportPageService $studentReportPageService,
        private readonly StudentFinalReportActionService $studentFinalReportActionService,
        private readonly StudentFinalReportFileService $studentFinalReportFileService,
        private readonly StudentFinalReportTemplateService $studentFinalReportTemplateService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Modules/Wims/Mahasiswa/Laporan/Index', $this->studentReportPageService->build($request->user()->id));
    }

    public function store(Request $request): RedirectResponse
    {
        $registration = $this->studentFinalReportActionService->resolveLatestRegistration($request->user()->id);

        if (! $registration || ! $registration->isPostInternshipPhase()) {
            return back()->withErrors([
                'laporan_akhir' => 'Laporan akhir hanya dapat diunggah setelah periode PKL berakhir.',
            ]);
        }

        $validated = $request->validate([
            'laporan_akhir' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $this->studentFinalReportActionService->upload($registration, $validated['laporan_akhir']);

        return back()->with('success', 'Dokumen laporan akhir berhasil diunggah.');
    }

    public function viewFinalReport(Request $request): BinaryFileResponse
    {
        return $this->studentFinalReportFileService->view(
            $this->studentFinalReportFileService->resolveLatestRegistrationWithReport($request->user()->id),
        );
    }

    public function downloadFinalReport(Request $request): BinaryFileResponse
    {
        return $this->studentFinalReportFileService->download(
            $this->studentFinalReportFileService->resolveLatestRegistrationWithReport($request->user()->id),
        );
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return $this->studentFinalReportTemplateService->downloadActiveTemplate();
    }
}

