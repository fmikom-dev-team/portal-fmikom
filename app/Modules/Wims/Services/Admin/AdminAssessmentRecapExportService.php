<?php

namespace App\Modules\Wims\Services\Admin;

use App\Models\PendaftaranMagang;
use App\Modules\Wims\Support\AssessmentSummary;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class AdminAssessmentRecapExportService
{
    public function download(PendaftaranMagang $pendaftaran, string $role): HttpResponse
    {
        abort_unless(in_array($role, ['dosen', 'mitra'], true), 404);

        $pendaftaran->load([
            'mahasiswa.programStudi:id,nama',
            'perusahaan:id,nama',
            'dosenPembimbing:id,name',
            'assessmentSubmissions' => fn ($builder) => AssessmentSummary::orderLatestFirst($builder)
                ->with([
                    'template.components',
                    'scores' => fn ($query) => $query->orderBy('assessment_component_id'),
                    'assessor:id,name',
                ])
                ->where('assessor_role', $role),
        ]);

        $submission = AssessmentSummary::latestSubmission($pendaftaran->assessmentSubmissions, $role);

        abort_if($submission === null || $submission->status !== 'submitted', 404, 'Data penilaian belum tersedia untuk diunduh.');

        $template = $submission->template;

        abort_if($template === null, 404, 'Template penilaian tidak ditemukan.');

        $scoreMap = $submission->scores->keyBy('assessment_component_id');
        $rows = $template->components->values()->map(function ($component, int $index) use ($scoreMap): array {
            $score = $scoreMap->get($component->id)?->score;

            return [
                'number' => $index + 1,
                'component' => $component->name,
                'weight_percentage' => (float) $component->weight_percentage,
                'score' => $score !== null ? number_format((float) $score, 2, '.', '') : '-',
            ];
        });

        return Pdf::loadView('pdf.assessment-sheet', [
            'title' => $role === 'dosen' ? 'Penilaian Dosen Pembimbing Lapangan' : 'Penilaian Mitra Perusahaan',
            'student' => [
                'name' => $pendaftaran->mahasiswa?->name ?? '-',
                'nim' => $pendaftaran->mahasiswa?->nim_nip ?: $pendaftaran->mahasiswa?->nomor_induk ?: '-',
                'program_studi' => $pendaftaran->mahasiswa?->programStudi?->nama ?? '-',
            ],
            'rows' => $rows,
            'total_weight' => round((float) $template->components->sum('weight_percentage'), 2),
            'total_score' => number_format((float) $submission->total_score, 2, '.', ''),
            'signer_label' => $role === 'dosen' ? 'Dosen Pembimbing Lapangan' : 'Pembimbing Lapangan Mitra',
            'signer_name' => $submission->assessor?->name ?? '-',
            'year' => $submission->submitted_at?->format('Y') ?? now()->format('Y'),
        ])
            ->setPaper('a4', 'portrait')
            ->download(
                sprintf(
                    'penilaian-%s-%s.pdf',
                    $role,
                    str($pendaftaran->mahasiswa?->name ?? 'mahasiswa')->slug(),
                ),
            );
    }
}
