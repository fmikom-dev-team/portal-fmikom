<?php

namespace App\Modules\Wims\Services\Shared\Assessment;

use App\Models\AssessmentSubmission;
use App\Models\AssessmentTemplate;
use App\Models\Magang\PendaftaranMagang;

class AssessmentShowService
{
    public function buildPayload(
        PendaftaranMagang $pendaftaran,
        ?AssessmentTemplate $template,
        ?AssessmentSubmission $submission,
        string $viewRouteName,
        string $downloadRouteName,
        string $statusLabelKey = 'status_label',
    ): array {
        if ($template) {
            $template->loadMissing('components');
            $submission?->loadMissing('scores');
        }

        $scoreMap = $submission?->scores?->keyBy('assessment_component_id') ?? collect();

        return [
            'student' => [
                'pendaftaran_id' => $pendaftaran->id,
                'mahasiswa_id' => $pendaftaran->mahasiswa?->id,
                'name' => $pendaftaran->mahasiswa?->name,
                'nim' => $pendaftaran->mahasiswa?->nim_nip ?: $pendaftaran->mahasiswa?->nomor_induk,
                'email' => $pendaftaran->mahasiswa?->email,
                'company' => $pendaftaran->perusahaan?->nama,
                'status_pendaftaran' => $pendaftaran->status,
                'period_label' => $this->formatPeriodLabel($pendaftaran),
                'final_report' => $pendaftaran->laporan_akhir_path ? [
                    'name' => $pendaftaran->laporan_akhir_original_name,
                    'view_url' => route($viewRouteName, $pendaftaran),
                    'download_url' => route($downloadRouteName, $pendaftaran),
                    'uploaded_at' => $pendaftaran->laporan_akhir_uploaded_at?->translatedFormat('d M Y H:i'),
                ] : null,
            ],
            'template_year' => $pendaftaran->tanggal_mulai?->format('Y'),
            'template' => $template
                ? [
                    'id' => $template->id,
                    'name' => $template->name,
                    'description' => $template->description,
                    'period_label' => sprintf(
                        '%s - %s',
                        $template->periode_mulai?->translatedFormat('d M Y'),
                        $template->periode_selesai?->translatedFormat('d M Y'),
                    ),
                    'total_weight' => round($template->components->sum('weight_percentage'), 2),
                    'components' => $template->components->map(fn ($component, $index) => [
                        'id' => $component->id,
                        'no' => $index + 1,
                        'name' => $component->name,
                        'description' => $component->description,
                        'weight_percentage' => (float) $component->weight_percentage,
                        'score' => $scoreMap->get($component->id)?->score,
                        'weighted_score' => $scoreMap->get($component->id)?->weighted_score,
                        'note' => $scoreMap->get($component->id)?->note,
                    ])->values()->all(),
                ]
                : null,
            'submission' => $submission
                ? [
                    'id' => $submission->id,
                    'status' => $submission->status,
                    $statusLabelKey => $this->resolveSubmissionStatusLabel($submission->status),
                    'total_score' => $submission->total_score,
                    'notes' => $submission->notes,
                    'submitted_at' => $submission->submitted_at?->translatedFormat('d M Y H:i'),
                ]
                : null,
        ];
    }

    private function formatPeriodLabel(PendaftaranMagang $pendaftaran): string
    {
        if (! $pendaftaran->tanggal_mulai || ! $pendaftaran->tanggal_selesai) {
            return 'Periode belum ditentukan';
        }

        return sprintf(
            '%s - %s',
            $pendaftaran->tanggal_mulai->translatedFormat('d M Y'),
            $pendaftaran->tanggal_selesai->translatedFormat('d M Y'),
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
