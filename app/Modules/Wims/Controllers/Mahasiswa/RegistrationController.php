<?php

namespace App\Modules\Wims\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Modules\Wims\Requests\Mahasiswa\StoreRegistrationRequest;
use App\Modules\Wims\Services\Mahasiswa\Registration\StudentRegistrationActionService;
use App\Modules\Wims\Services\Mahasiswa\Registration\StudentRegistrationPageService;
use App\Modules\Wims\Services\Mahasiswa\Report\StudentFinalReportTemplateService;
use App\Services\VirusScannerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly StudentRegistrationPageService $studentRegistrationPageService,
        private readonly StudentRegistrationActionService $studentRegistrationActionService,
        private readonly StudentFinalReportTemplateService $studentFinalReportTemplateService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Modules/Wims/Mahasiswa/Pendaftaran/Index', $this->studentRegistrationPageService->build($request->user()));
    }

    public function downloadProposalTemplate(): BinaryFileResponse
    {
        return $this->studentFinalReportTemplateService->downloadActiveTemplate('proposal');
    }

    public function store(StoreRegistrationRequest $request): RedirectResponse
    {
        $user = $request->user();
        $requestedRegistration = $request->integer('registration_id')
            ? $this->studentRegistrationPageService->registrationForStudent($user->id, $request->integer('registration_id'))
            : null;
        // Only a revision reuses the selected record. A new submission after
        // completion/rejection must always start with fresh attachments.
        $actionRegistration = $requestedRegistration?->status === 'revisi'
            ? $requestedRegistration
            : $this->studentRegistrationPageService->latestRegistration($user->id);
        $proposalFile = $request->file('proposal_pkl');
        $transcriptFile = $request->file('transkrip_nilai');
        $recommendationFile = $request->file('surat_rekomendasi_kaprodi');
        $removeRecommendation = $request->boolean('surat_rekomendasi_kaprodi_remove');

        $missingFiles = [];
        $isRevision = $actionRegistration?->status === 'revisi';

        if (! $proposalFile && (! $isRevision || ! filled($actionRegistration->proposal_pkl_path))) {
            $missingFiles['proposal_pkl'] = 'Proposal PKL wajib dilampirkan saat pendaftaran.';
        }
        if (! $transcriptFile && (! $isRevision || ! filled($actionRegistration->transkrip_nilai_path))) {
            $missingFiles['transkrip_nilai'] = 'Transkrip nilai terakhir wajib dilampirkan.';
        }
        if ($missingFiles) {
            throw ValidationException::withMessages($missingFiles);
        }

        foreach (array_filter([
            'proposal_pkl' => $proposalFile,
            'transkrip_nilai' => $transcriptFile,
            'surat_rekomendasi_kaprodi' => $recommendationFile,
        ]) as $field => $file) {
            $scanner = app(VirusScannerService::class);
            $scanResult = $scanner->scan($file);
            if (! $scanResult['safe']) {
                throw ValidationException::withMessages([
                    $field => $scanResult['reason'],
                ]);
            }
        }

        if (! $this->studentRegistrationPageService->canSubmitRegistration($actionRegistration)) {
            return back()->withErrors([
                'registration' => 'Pendaftaran sedang menunggu keputusan kampus, periode magang belum selesai, atau penilaian akhir belum lengkap.',
            ]);
        }

        $payload = $this->studentRegistrationActionService->buildPayload([
            'tanggal_mulai' => $request->date('tanggal_mulai')?->toDateString(),
            'tanggal_selesai' => $request->date('tanggal_selesai')?->toDateString(),
            'perusahaan_diminati_nama' => $request->safe()->string('perusahaan_diminati_nama')->trim()->toString(),
            'perusahaan_diminati_alamat' => $request->safe()->string('perusahaan_diminati_alamat')->trim()->toString(),
            'catatan_pengajuan' => $request->safe()->string('catatan_pengajuan')->trim()->toString(),
            'status_kip' => $request->safe()->string('status_kip')->toString(),
            'sks_ditempuh' => $request->integer('sks_ditempuh'),
            'bidang_minat' => $request->safe()->string('bidang_minat')->trim()->toString(),
            'bidang_minat_lainnya' => $request->safe()->string('bidang_minat_lainnya')->trim()->toString(),
            'ukuran_seragam' => $request->safe()->string('ukuran_seragam')->toString(),
            'ukuran_seragam_custom' => $request->safe()->string('ukuran_seragam_custom')->trim()->toString(),
        ]);

        if ($isRevision) {
            $this->studentRegistrationActionService->resubmitRevision($actionRegistration, $payload, $proposalFile, $transcriptFile, $recommendationFile, $removeRecommendation);

            return to_route('wims.registration', ['pendaftaran' => $actionRegistration->id])
                ->with('success', 'Perbaikan pendaftaran berhasil dikirim ulang dan menunggu review kampus.');
        }

        $registration = $this->studentRegistrationActionService->create($user, $payload, $proposalFile, $transcriptFile, $recommendationFile);

        return to_route('wims.registration', ['pendaftaran' => $registration->id])
            ->with('success', 'Pendaftaran PKL/magang berhasil dikirim dan menunggu review kampus.');
    }
}
