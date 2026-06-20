<?php

namespace App\Modules\Wims\Services\Shared\Assessment;

use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use App\Modules\Wims\Support\AssessmentSummary;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class AssessmentIndexService
{
    public function buildLecturerData(User $user): array
    {
        $pendaftarans = PendaftaranMagang::query()
            ->with([
                'mahasiswa:id,name,email,nomor_induk',
                'perusahaan:id,nama',
                'assessmentSubmissions' => fn ($query) => AssessmentSummary::orderLatestFirst($query)
                    ->where('assessor_id', $user->id)
                    ->where('assessor_role', 'dosen')
                    ->with('template:id,name'),
            ])
            ->where('dosen_pembimbing_id', $user->id)
            ->readyForAssessment(now())
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->get()
            ->map(function (PendaftaranMagang $pendaftaran) {
                $submission = AssessmentSummary::latestSubmission(
                    $pendaftaran->assessmentSubmissions,
                    'dosen',
                    $pendaftaran->dosen_pembimbing_id,
                );

                return [
                    'id' => $pendaftaran->id,
                    'student' => [
                        'name' => $pendaftaran->mahasiswa?->name,
                        'nim' => $pendaftaran->mahasiswa?->nim_nip ?: $pendaftaran->mahasiswa?->nomor_induk,
                        'email' => $pendaftaran->mahasiswa?->email,
                    ],
                    'company' => [
                        'id' => $pendaftaran->perusahaan?->id,
                        'name' => $pendaftaran->perusahaan?->nama,
                    ],
                    'period' => [
                        'start' => $pendaftaran->tanggal_mulai?->toDateString(),
                        'end' => $pendaftaran->tanggal_selesai?->toDateString(),
                        'label' => $this->formatPeriodLabel($pendaftaran),
                    ],
                    'registration_status' => $pendaftaran->status,
                    'assessment' => [
                        'status_key' => $submission?->status ?? 'not_assessed',
                        'status_label' => $this->resolveSubmissionStatusLabel($submission?->status),
                        'total_score' => $submission?->total_score !== null
                            ? round((float) $submission->total_score, 2)
                            : null,
                        'submitted_at' => $submission?->submitted_at?->translatedFormat('d M Y H:i'),
                        'template_name' => $submission?->template?->name,
                    ],
                ];
            })
            ->values();

        return $this->buildIndexPayload($pendaftarans);
    }

    public function buildCompanyData(User $user, ?PerusahaanMitra $company): array
    {
        if (! $company) {
            return [
                'summary' => [
                    'total_students' => 0,
                    'not_assessed' => 0,
                    'draft' => 0,
                    'submitted' => 0,
                ],
                'students' => [],
            ];
        }

        $pendaftarans = PendaftaranMagang::query()
            ->with([
                'mahasiswa:id,name,email,nomor_induk',
                'perusahaan:id,nama',
                'assessmentSubmissions' => fn ($query) => AssessmentSummary::orderLatestFirst($query)
                    ->where('assessor_id', $user->id)
                    ->where('assessor_role', 'mitra')
                    ->with('template:id,name'),
            ])
            ->where('perusahaan_id', $company->id)
            ->readyForAssessment(now())
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->get()
            ->map(function (PendaftaranMagang $pendaftaran) use ($user) {
                $submission = AssessmentSummary::latestSubmission(
                    $pendaftaran->assessmentSubmissions,
                    'mitra',
                    $user->id,
                );
                $statusKey = $submission?->status ?? 'not_assessed';

                return [
                    'id' => $pendaftaran->id,
                    'student' => [
                        'name' => $pendaftaran->mahasiswa?->name,
                        'nim' => $pendaftaran->mahasiswa?->nim_nip ?: $pendaftaran->mahasiswa?->nomor_induk,
                        'email' => $pendaftaran->mahasiswa?->email,
                    ],
                    'company' => [
                        'id' => $pendaftaran->perusahaan?->id,
                        'name' => $pendaftaran->perusahaan?->nama,
                    ],
                    'period' => [
                        'start' => $pendaftaran->tanggal_mulai?->toDateString(),
                        'end' => $pendaftaran->tanggal_selesai?->toDateString(),
                        'label' => $this->formatPeriodLabel($pendaftaran),
                    ],
                    'registration_status' => $pendaftaran->status,
                    'assessment' => [
                        'status_key' => $statusKey,
                        'status_label' => $this->resolveSubmissionStatusLabel($statusKey),
                        'total_score' => $submission?->total_score !== null
                            ? round((float) $submission->total_score, 2)
                            : null,
                        'submitted_at' => $submission?->submitted_at?->translatedFormat('d M Y H:i'),
                        'template_name' => $submission?->template?->name,
                    ],
                ];
            })
            ->values();

        return $this->buildIndexPayload($pendaftarans);
    }

    private function buildIndexPayload(Collection $pendaftarans): array
    {
        return [
            'summary' => [
                'total_students' => $pendaftarans->count(),
                'not_assessed' => $pendaftarans->where('assessment.status_key', 'not_assessed')->count(),
                'draft' => $pendaftarans->where('assessment.status_key', 'draft')->count(),
                'submitted' => $pendaftarans->where('assessment.status_key', 'submitted')->count(),
            ],
            'students' => $pendaftarans->all(),
        ];
    }

    private function formatPeriodLabel(PendaftaranMagang $pendaftaran): string
    {
        if (! $pendaftaran->tanggal_mulai || ! $pendaftaran->tanggal_selesai) {
            return 'Periode belum ditentukan';
        }

        return sprintf(
            '%s - %s',
            Carbon::parse($pendaftaran->tanggal_mulai)->translatedFormat('d M Y'),
            Carbon::parse($pendaftaran->tanggal_selesai)->translatedFormat('d M Y'),
        );
    }

    private function resolveSubmissionStatusLabel(?string $status): string
    {
        return match ($status) {
            'submitted' => 'Sudah Dikirim',
            'draft' => 'Draft',
            default => 'Belum Dinilai',
        };
    }
}
