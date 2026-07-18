<?php

namespace App\Modules\Wims\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Modules\Wims\Services\Mahasiswa\Report\StudentFinalReportActionService;
use App\Modules\Wims\Services\Mahasiswa\Report\StudentFinalReportFileService;
use App\Modules\Wims\Services\Mahasiswa\Report\StudentFinalReportTemplateService;
use App\Modules\Wims\Services\Mahasiswa\Report\StudentReportPageService;
use App\Services\VirusScannerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
        return Inertia::render('Modules/Wims/Mahasiswa/Laporan/Index', $this->studentReportPageService->build(
            $request->user()->id,
            $request->integer('pendaftaran'),
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $registration = $this->studentFinalReportActionService->resolveRegistration(
            $request->user()->id,
            $request->integer('pendaftaran_id'),
        );

        if (! $registration || ! $registration->isPostInternshipPhase()) {
            return back()->withErrors([
                'laporan_akhir' => 'Laporan akhir hanya dapat diunggah setelah periode PKL berakhir.',
            ]);
        }

        $validated = $request->validate([
            'laporan_akhir' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $scanner = app(VirusScannerService::class);
        $scanResult = $scanner->scan($validated['laporan_akhir']);
        if (! $scanResult['safe']) {
            throw ValidationException::withMessages([
                'laporan_akhir' => $scanResult['reason'],
            ]);
        }

        $this->studentFinalReportActionService->upload($registration, $validated['laporan_akhir']);

        return back()->with('success', 'Dokumen laporan akhir berhasil diunggah.');
    }

    public function viewFinalReport(Request $request): BinaryFileResponse
    {
        return $this->studentFinalReportFileService->view(
            $this->studentFinalReportFileService->resolveRegistrationWithReport(
                $request->user()->id,
                $request->integer('pendaftaran'),
            ),
        );
    }

    public function downloadFinalReport(Request $request): BinaryFileResponse
    {
        return $this->studentFinalReportFileService->download(
            $this->studentFinalReportFileService->resolveRegistrationWithReport(
                $request->user()->id,
                $request->integer('pendaftaran'),
            ),
        );
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return $this->studentFinalReportTemplateService->downloadActiveTemplate('final_report');
    }
}
