<?php

namespace App\Modules\Wims\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Magang\PendaftaranMagang;
use App\Models\User;
use App\Modules\Wims\Services\Shared\Assessment\AssessmentIndexService;
use App\Modules\Wims\Services\Shared\Assessment\AssessmentShowService;
use App\Modules\Wims\Services\Shared\Assessment\AssessmentSubmissionService;
use App\Modules\Wims\Services\Shared\Assessment\AssessmentTemplateResolverService;
use App\Modules\Wims\Services\Dosen\LecturerAssessmentWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PenilaianMahasiswaController extends Controller
{
    public function __construct(
        private readonly AssessmentIndexService $assessmentIndexService,
        private readonly AssessmentShowService $assessmentShowService,
        private readonly AssessmentSubmissionService $assessmentSubmissionService,
        private readonly AssessmentTemplateResolverService $assessmentTemplateResolverService,
        private readonly LecturerAssessmentWorkflowService $lecturerAssessmentWorkflowService,
    ) {
    }

    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $payload = $this->assessmentIndexService->buildLecturerData($user);

        return Inertia::render('Modules/Wims/Dosen/PenilaianMahasiswa/Index', [
            'summary' => $payload['summary'],
            'students' => $payload['students'],
        ]);
    }

    public function show(Request $request, PendaftaranMagang $pendaftaran): Response
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($this->lecturerAssessmentWorkflowService->isAuthorized($user, $pendaftaran), 403);
        abort_if(
            ! $this->lecturerAssessmentWorkflowService->canAssess($pendaftaran),
            403,
            'Penilaian hanya dapat dilakukan setelah PKL diselesaikan admin.',
        );

        $this->lecturerAssessmentWorkflowService->loadAssessmentRelations($pendaftaran);

        $submission = $this->assessmentSubmissionService->resolveLatestSubmission($pendaftaran, $user, 'dosen');
        $template = $this->assessmentTemplateResolverService->resolveForRegistration($pendaftaran, 'dosen', $submission);
        $payload = $this->assessmentShowService->buildPayload(
            $pendaftaran,
            $template,
            $submission,
            'wims.dosen.assessments.final-report.view',
            'wims.dosen.assessments.final-report.download',
        );

        return Inertia::render('Modules/Wims/Dosen/PenilaianMahasiswa/Show', $payload);
    }

    public function store(Request $request, PendaftaranMagang $pendaftaran): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($this->lecturerAssessmentWorkflowService->isAuthorized($user, $pendaftaran), 403);
        abort_if(
            ! $this->lecturerAssessmentWorkflowService->canAssess($pendaftaran),
            403,
            'Penilaian hanya dapat dilakukan setelah PKL selesai.',
        );

        $existingSubmission = $this->assessmentSubmissionService->resolveLatestSubmission($pendaftaran, $user, 'dosen');
        $template = $this->assessmentTemplateResolverService->resolveForRegistration($pendaftaran, 'dosen', $existingSubmission);

        if (! $template) {
            return back()->withErrors([
                'template' => $this->lecturerAssessmentWorkflowService->templateUnavailableMessage($pendaftaran),
            ]);
        }

        $template->load('components');
        $allowedComponentIds = $this->assessmentSubmissionService->allowedComponentIds($template);

        if ($this->lecturerAssessmentWorkflowService->hasSubmitted($existingSubmission)) {
            return back()->with('error', 'Nilai dosen sudah dikirim dan tidak dapat diubah.');
        }

        $validated = $this->lecturerAssessmentWorkflowService->validateSubmissionRequest($request, $allowedComponentIds);
        $this->assessmentSubmissionService->saveSubmission(
            $pendaftaran,
            $template,
            $user,
            'dosen',
            $existingSubmission,
            $validated,
        );

        return back()->with('success', $validated['action'] === 'submitted'
            ? 'Nilai dosen berhasil dikirim.'
            : 'Draft penilaian dosen berhasil disimpan.');
    }

    public function viewFinalReport(Request $request, PendaftaranMagang $pendaftaran): BinaryFileResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($this->lecturerAssessmentWorkflowService->isAuthorized($user, $pendaftaran), 403);
        abort_unless(filled($pendaftaran->laporan_akhir_path), 404);

        $absolutePath = $this->lecturerAssessmentWorkflowService->resolveFinalReportPath($pendaftaran);
        abort_unless(is_file($absolutePath), 404);

        return response()->file($absolutePath, [
            'Content-Disposition' => 'inline; filename="' . $pendaftaran->finalReportDownloadName() . '"',
        ]);
    }

    public function downloadFinalReport(Request $request, PendaftaranMagang $pendaftaran): BinaryFileResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless($this->lecturerAssessmentWorkflowService->isAuthorized($user, $pendaftaran), 403);
        abort_unless(filled($pendaftaran->laporan_akhir_path), 404);

        $absolutePath = $this->lecturerAssessmentWorkflowService->resolveFinalReportPath($pendaftaran);
        abort_unless(is_file($absolutePath), 404);

        return response()->download($absolutePath, $pendaftaran->finalReportDownloadName());
    }
}
