<?php

namespace App\Modules\Wims\Services\Dosen;

use App\Models\Magang\AssessmentSubmission;
use App\Models\Magang\PendaftaranMagang;
use App\Models\User;
use App\Modules\Wims\Services\Shared\Assessment\FinalReportAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LecturerAssessmentWorkflowService
{
    public function __construct(
        private readonly FinalReportAccessService $finalReportAccessService,
    ) {
    }

    public function isAuthorized(User $user, PendaftaranMagang $pendaftaran): bool
    {
        return (int) $pendaftaran->dosen_pembimbing_id === (int) $user->id;
    }

    public function canAssess(PendaftaranMagang $pendaftaran): bool
    {
        return $pendaftaran->status === 'selesai';
    }

    public function loadAssessmentRelations(PendaftaranMagang $pendaftaran): void
    {
        $pendaftaran->load([
            'mahasiswa:id,name,email,nim_nip,nomor_induk',
            'perusahaan:id,nama',
        ]);
    }

    public function templateUnavailableMessage(PendaftaranMagang $pendaftaran): string
    {
        return sprintf(
            'Template penilaian tahun %s belum tersedia.',
            $pendaftaran->tanggal_mulai?->format('Y') ?? 'PKL ini',
        );
    }

    public function validateSubmissionRequest(Request $request, array $allowedComponentIds): array
    {
        return Validator::make($request->all(), [
            'scores' => ['required', 'array', 'min:' . count($allowedComponentIds)],
            'scores.*.component_id' => ['required', 'integer', Rule::in($allowedComponentIds)],
            'scores.*.score' => ['required', 'numeric', 'min:0', 'max:100'],
            'scores.*.note' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'action' => ['required', Rule::in(['draft', 'submitted'])],
        ])->validate();
    }

    public function hasSubmitted(?AssessmentSubmission $submission): bool
    {
        return $submission?->status === 'submitted';
    }

    public function resolveFinalReportPath(PendaftaranMagang $pendaftaran): string
    {
        $pendaftaran->loadMissing('mahasiswa');

        return $this->finalReportAccessService->resolveAbsolutePath($pendaftaran);
    }
}
