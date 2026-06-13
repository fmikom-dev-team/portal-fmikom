<?php

namespace App\Modules\Wims\Services\Shared\Monitoring;

use App\Models\AbsensiMagang;
use App\Models\AssessmentSubmission;
use App\Models\LogbookMagang;
use App\Models\PendaftaranMagang;
use App\Modules\Wims\Support\AssessmentSummary;
use App\Models\User;

class MonitoringSummaryService
{
    public function buildAttendanceSummary(PendaftaranMagang $pendaftaran): array
    {
        $attendanceSummary = AbsensiMagang::query()
            ->where('pendaftaran_id', $pendaftaran->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as total_terlambat")
            ->selectRaw("SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as total_tepat_waktu")
            ->selectRaw("SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as total_izin")
            ->selectRaw("SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as total_sakit")
            ->selectRaw("SUM(CASE WHEN status = 'alfa' THEN 1 ELSE 0 END) as total_alfa")
            ->first();

        return [
            'total' => (int) ($attendanceSummary?->total ?? 0),
            'total_tepat_waktu' => (int) ($attendanceSummary?->total_tepat_waktu ?? 0),
            'total_terlambat' => (int) ($attendanceSummary?->total_terlambat ?? 0),
            'total_izin' => (int) ($attendanceSummary?->total_izin ?? 0),
            'total_sakit' => (int) ($attendanceSummary?->total_sakit ?? 0),
            'total_alfa' => (int) ($attendanceSummary?->total_alfa ?? 0),
        ];
    }

    public function buildLogbookSummary(PendaftaranMagang $pendaftaran): array
    {
        $logbookSummary = LogbookMagang::query()
            ->where('pendaftaran_id', $pendaftaran->id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'disetujui' THEN 1 ELSE 0 END) as total_disetujui")
            ->selectRaw("SUM(CASE WHEN status = 'revisi' THEN 1 ELSE 0 END) as total_revisi")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as total_pending")
            ->first();

        return [
            'total' => (int) ($logbookSummary?->total ?? 0),
            'total_disetujui' => (int) ($logbookSummary?->total_disetujui ?? 0),
            'total_revisi' => (int) ($logbookSummary?->total_revisi ?? 0),
            'total_pending' => (int) ($logbookSummary?->total_pending ?? 0),
        ];
    }

    public function buildAssessmentSummary(PendaftaranMagang $pendaftaran, User $user, string $role): array
    {
        $assessmentSubmission = AssessmentSubmission::query()
            ->where('pendaftaran_magang_id', $pendaftaran->id)
            ->where('assessor_id', $user->id)
            ->where('assessor_role', $role)
            ->tap(fn ($query) => AssessmentSummary::orderLatestFirst($query))
            ->first();

        return [
            'status' => $assessmentSubmission?->status ?? 'not_assessed',
            'status_label' => $this->resolveAssessmentStatusLabel($assessmentSubmission?->status),
            'total_score' => $assessmentSubmission?->total_score,
            'submitted_at' => $assessmentSubmission?->submitted_at?->translatedFormat('d M Y H:i'),
        ];
    }

    private function resolveAssessmentStatusLabel(?string $status): string
    {
        return match ($status) {
            'submitted' => 'Sudah Dikirim',
            'draft' => 'Draft',
            default => 'Belum Dinilai',
        };
    }
}
