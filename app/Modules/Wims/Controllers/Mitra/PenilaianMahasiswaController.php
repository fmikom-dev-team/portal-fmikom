<?php

namespace App\Modules\Wims\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use App\Modules\Wims\Services\Mitra\MitraAccessService;
use App\Modules\Wims\Services\Shared\Assessment\AssessmentIndexService;
use App\Modules\Wims\Services\Shared\Assessment\AssessmentShowService;
use App\Modules\Wims\Services\Shared\Assessment\AssessmentSubmissionService;
use App\Modules\Wims\Services\Shared\Assessment\AssessmentTemplateResolverService;
use App\Modules\Wims\Services\Shared\Assessment\FinalReportAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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
        private readonly FinalReportAccessService $finalReportAccessService,
        private readonly MitraAccessService $mitraAccessService,
    ) {}

    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();
        $company = $this->mitraAccessService->resolveCompany($user);

        $payload = $this->assessmentIndexService->buildCompanyData($user, $company);

        return Inertia::render('Modules/Wims/Mitra/PenilaianMahasiswa/Index', [
            'summary' => $payload['summary'],
            'students' => $payload['students'],
        ]);
    }

    public function show(Request $request, PendaftaranMagang $pendaftaran): Response
    {
        /** @var User $user */
        $user = $request->user();

        $company = $this->mitraAccessService->resolveCompany($user);
        abort_unless($company !== null, 403);

        $authorizedPendaftaran = $this->resolveAuthorizedPendaftaran($company, $pendaftaran->id);
        abort_unless($authorizedPendaftaran !== null, 403);
        abort_if(
            ! $authorizedPendaftaran->isReadyForAssessment(now()),
            403,
            'Penilaian hanya dapat dilakukan setelah PKL memasuki tahap penilaian.',
        );

        $authorizedPendaftaran->load([
            'mahasiswa:id,name,email,nomor_induk',
            'perusahaan:id,nama',
        ]);

        $submission = $this->assessmentSubmissionService->resolveLatestSubmission($authorizedPendaftaran, $user, 'mitra');
        $template = $this->assessmentTemplateResolverService->resolveForRegistration($authorizedPendaftaran, 'mitra', $submission);
        $payload = $this->assessmentShowService->buildPayload(
            $authorizedPendaftaran,
            $template,
            $submission,
            'wims.mitra.assessments.final-report.view',
            'wims.mitra.assessments.final-report.download',
        );

        return Inertia::render('Modules/Wims/Mitra/PenilaianMahasiswa/Show', $payload);
    }

    public function store(Request $request, PendaftaranMagang $pendaftaran): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $company = $this->mitraAccessService->resolveCompany($user);
        abort_unless($company !== null, 403);

        $authorizedPendaftaran = $this->resolveAuthorizedPendaftaran($company, $pendaftaran->id);
        abort_unless($authorizedPendaftaran !== null, 403);
        abort_if(
            ! $authorizedPendaftaran->isReadyForAssessment(now()),
            403,
            'Penilaian hanya dapat dilakukan setelah PKL memasuki tahap penilaian.',
        );

        $existingSubmission = $this->assessmentSubmissionService->resolveLatestSubmission($authorizedPendaftaran, $user, 'mitra');
        $template = $this->assessmentTemplateResolverService->resolveForRegistration($authorizedPendaftaran, 'mitra', $existingSubmission);

        if (! $template) {
            return back()->withErrors([
                'template' => sprintf(
                    'Template penilaian mitra untuk periode %s belum tersedia.',
                    $authorizedPendaftaran->tanggal_mulai?->format('Y') ?? 'PKL ini',
                ),
            ]);
        }

        $template->load('components');
        $allowedComponentIds = $this->assessmentSubmissionService->allowedComponentIds($template);

        $validated = Validator::make($request->all(), [
            'scores' => ['required', 'array', 'min:1'],
            'scores.*.component_id' => ['required', 'integer', 'distinct', Rule::in($allowedComponentIds)],
            'scores.*.score' => ['required', 'numeric', 'min:0', 'max:100'],
            'scores.*.note' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'action' => ['required', Rule::in(['draft', 'submitted'])],
        ])->validate();

        if ($existingSubmission?->status === 'submitted') {
            return back()->with('error', 'Nilai mitra sudah dikirim dan tidak dapat diubah lagi.');
        }

        $this->assessmentSubmissionService->saveSubmission(
            $authorizedPendaftaran,
            $template,
            $user,
            'mitra',
            $existingSubmission,
            $validated,
        );

        return back()->with('success', $validated['action'] === 'submitted'
            ? 'Nilai mitra berhasil dikirim.'
            : 'Draft penilaian mitra berhasil disimpan.');
    }

    public function viewFinalReport(Request $request, PendaftaranMagang $pendaftaran): BinaryFileResponse
    {
        /** @var User $user */
        $user = $request->user();

        $company = $this->mitraAccessService->resolveCompany($user);
        abort_unless($company !== null, 403);

        $authorizedPendaftaran = $this->resolveAuthorizedPendaftaran($company, $pendaftaran->id);
        abort_unless($authorizedPendaftaran !== null, 403);
        abort_unless(filled($authorizedPendaftaran->laporan_akhir_path), 404);

        $authorizedPendaftaran->loadMissing('mahasiswa');
        $absolutePath = $this->finalReportAccessService->resolveAbsolutePath($authorizedPendaftaran);
        abort_unless(is_file($absolutePath), 404);

        return response()->file($absolutePath, [
            'Content-Disposition' => 'inline; filename="'.$authorizedPendaftaran->finalReportDownloadName().'"',
        ]);
    }

    public function downloadFinalReport(Request $request, PendaftaranMagang $pendaftaran): BinaryFileResponse
    {
        /** @var User $user */
        $user = $request->user();

        $company = $this->mitraAccessService->resolveCompany($user);
        abort_unless($company !== null, 403);

        $authorizedPendaftaran = $this->resolveAuthorizedPendaftaran($company, $pendaftaran->id);
        abort_unless($authorizedPendaftaran !== null, 403);
        abort_unless(filled($authorizedPendaftaran->laporan_akhir_path), 404);

        $authorizedPendaftaran->loadMissing('mahasiswa');
        $absolutePath = $this->finalReportAccessService->resolveAbsolutePath($authorizedPendaftaran);
        abort_unless(is_file($absolutePath), 404);

        return response()->download($absolutePath, $authorizedPendaftaran->finalReportDownloadName());
    }

    private function resolveAuthorizedPendaftaran(PerusahaanMitra $company, int $pendaftaranId): ?PendaftaranMagang
    {
        return PendaftaranMagang::query()
            ->where('id', $pendaftaranId)
            ->where('perusahaan_id', $company->id)
            ->first();
    }
}
