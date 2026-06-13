<?php

namespace App\Http\Controllers\Wims\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Services\Wims\Mahasiswa\Report\StudentFinalReportActionService;
use App\Services\Wims\Mahasiswa\Report\StudentFinalReportFileService;
use App\Services\Wims\Mahasiswa\Report\StudentReportPageService;
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
    ) {
    }

    public function index(Request $request): Response
    {
        return Inertia::render('Wims/Mahasiswa/Laporan', $this->studentReportPageService->build($request->user()->id));
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
            'laporan_akhir' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
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
}
