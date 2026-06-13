<?php

namespace App\Modules\Wims\Services\Mahasiswa\Report;

use App\Models\Magang\AbsensiMagang;
use App\Models\Magang\LogbookMagang;
use App\Models\Magang\PendaftaranMagang;
use App\Modules\Wims\Support\AssessmentSummary;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

class StudentReportPageService
{
    public function build(int $userId): array
    {
        $registration = PendaftaranMagang::query()
            ->with([
                'perusahaan.user',
                'dosenPembimbing',
                'assessmentSubmissions' => fn ($query) => $query
                    ->whereIn('assessor_role', ['dosen', 'mitra'])
                    ->orderByDesc('submitted_at')
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id'),
            ])
            ->forMahasiswa($userId)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->first();
        $historyRegistration = $registration && (
            $registration->status === 'aktif' || $registration->isPostInternshipPhase()
        ) ? $registration : null;

        $pageState = 'not_registered';

        if ($registration?->isPostInternshipPhase()) {
            $pageState = 'completed';
        } elseif ($registration?->status === 'aktif') {
            $pageState = 'active';
        } elseif ($registration?->status) {
            $pageState = 'waiting';
        }

        [$progressPercentage, $totalDays, $completedDays, $remainingDays] = $this->buildInternshipProgress($registration);

        $assessmentSummary = $registration
            ? AssessmentSummary::fromSubmissions($registration->assessmentSubmissions)
            : null;
        $attendanceHistory = $historyRegistration
            ? AbsensiMagang::query()
                ->where('pendaftaran_id', $historyRegistration->id)
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->get()
            : collect();
        $logbookHistory = $historyRegistration
            ? LogbookMagang::query()
                ->with('photos')
                ->where('pendaftaran_id', $historyRegistration->id)
                ->orderByDesc('tanggal')
                ->orderByDesc('id')
                ->get()
            : collect();
        $attendanceTotals = $attendanceHistory->groupBy(fn (AbsensiMagang $item) => (string) $item->status);

        return [
            'pageState' => $pageState,
            'registration' => $registration ? [
                'status' => $registration->status,
                'company_name' => $registration->perusahaan?->nama,
                'proposal_company_name' => $registration->perusahaan_diminati_nama,
                'dosen_name' => $registration->dosenPembimbing?->name,
                'mentor_name' => $registration->perusahaan?->user?->name ?? '-',
                'period_label' => $registration->tanggal_mulai && $registration->tanggal_selesai
                    ? $this->formatLocalizedDate($registration->tanggal_mulai, 'd M Y')
                        . ' - '
                        . $this->formatLocalizedDate($registration->tanggal_selesai, 'd M Y')
                    : null,
                'submitted_at' => $this->formatLocalizedDate($registration->created_at, 'd M Y H:i'),
                'laporan_akhir' => $registration->laporan_akhir_path ? [
                    'name' => $registration->laporan_akhir_original_name,
                    'view_url' => route('wims.laporan.final-report.view'),
                    'download_url' => route('wims.laporan.final-report.download'),
                    'uploaded_at' => $this->formatLocalizedDate($registration->laporan_akhir_uploaded_at, 'd M Y H:i'),
                ] : null,
            ] : null,
            'internship' => [
                'progress_percentage' => $progressPercentage,
                'completed_days' => $completedDays,
                'total_days' => $totalDays,
                'remaining_days' => $remainingDays,
                'total_logbook_entries' => $logbookHistory->count(),
                'total_hadir' => ($attendanceTotals->get('hadir')?->count() ?? 0) + ($attendanceTotals->get('tepat_waktu')?->count() ?? 0) + ($attendanceTotals->get('terlambat')?->count() ?? 0),
                'total_izin' => $attendanceTotals->get('izin')?->count() ?? 0,
                'total_sakit' => $attendanceTotals->get('sakit')?->count() ?? 0,
            ],
            'evaluation' => [
                'status_penilaian' => $this->mapLegacyAssessmentStatus($assessmentSummary['status_key'] ?? null),
                'nilai_akhir' => $assessmentSummary['dosen']['score'] ?? null,
                'tanggal_nilai' => $this->formatLocalizedDate($assessmentSummary['dosen']['submitted_at'] ?? null, 'd M Y H:i'),
                'finalized_at' => $this->formatLocalizedDate(
                    ($assessmentSummary['is_complete'] ?? false) ? ($assessmentSummary['latest_submitted_at'] ?? null) : null,
                    'd M Y H:i',
                ),
                'catatan_dosen' => $assessmentSummary['dosen']['notes'] ?? null,
                'status_key' => $assessmentSummary['status_key'] ?? 'not_assessed',
                'status_label' => $assessmentSummary['status_label'] ?? 'Belum Dinilai',
                'is_complete' => $assessmentSummary['is_complete'] ?? false,
                'dosen_score' => $assessmentSummary['dosen']['score'] ?? null,
                'mitra_score' => $assessmentSummary['mitra']['score'] ?? null,
                'dosen_submitted_at' => $this->formatLocalizedDate($assessmentSummary['dosen']['submitted_at'] ?? null, 'd M Y H:i'),
                'mitra_submitted_at' => $this->formatLocalizedDate($assessmentSummary['mitra']['submitted_at'] ?? null, 'd M Y H:i'),
                'latest_submitted_at' => $this->formatLocalizedDate($assessmentSummary['latest_submitted_at'] ?? null, 'd M Y H:i'),
                'dosen_note' => $assessmentSummary['dosen']['notes'] ?? null,
            ],
            'history' => [
                'attendance' => $attendanceHistory
                    ->map(fn (AbsensiMagang $attendance) => [
                        'id' => $attendance->id,
                        'tanggal' => $attendance->tanggal?->toDateString(),
                        'tanggal_label' => $this->formatLocalizedDate($attendance->tanggal, 'd M Y'),
                        'check_in' => $attendance->timestamp_masuk?->format('H:i:s'),
                        'check_out' => $attendance->timestamp_keluar?->format('H:i:s'),
                        'check_in_photo_url' => $attendance->checkInPhotoUrl(),
                        'check_out_photo_url' => $attendance->checkOutPhotoUrl(),
                        'status' => $attendance->status,
                        'keterangan' => is_null($attendance->lokasi_valid)
                            ? null
                            : ($attendance->lokasi_valid ? 'Lokasi tervalidasi' : 'Lokasi belum tervalidasi'),
                    ])
                    ->values()
                    ->all(),
                'logbook' => $logbookHistory
                    ->map(fn (LogbookMagang $logbook) => [
                        'id' => $logbook->id,
                        'tanggal' => $logbook->tanggal?->toDateString(),
                        'tanggal_label' => $this->formatLocalizedDate($logbook->tanggal, 'd M Y'),
                        'jam_mulai' => $this->formatLocalizedDate($logbook->jam_mulai, 'H:i'),
                        'jam_selesai' => $this->formatLocalizedDate($logbook->jam_selesai, 'H:i'),
                        'aktivitas' => $logbook->aktivitas_harian,
                        'kompetensi' => $logbook->kompetensi_dicapai,
                        'status' => $logbook->status,
                        'catatan_mitra' => $logbook->catatan_mitra ?? $logbook->catatan_dosen,
                        'photos' => $logbook->photos
                            ->map(fn ($photo) => [
                                'id' => $photo->id,
                                'url' => '/storage/' . ltrim((string) $photo->file_path, '/'),
                            ])
                            ->values()
                            ->all(),
                    ])
                    ->values()
                    ->all(),
            ],
            'currentPeriodHistoryDownloadUrl' => $historyRegistration?->id
                ? route('wims.absensi.download', ['scope' => 'current'])
                : null,
            'currentPeriodLogbookDownloadUrl' => $historyRegistration?->id
                ? route('wims.logbook.download')
                : null,
        ];
    }

    private function buildInternshipProgress(?PendaftaranMagang $registration): array
    {
        $progressPercentage = 0;
        $totalDays = null;
        $completedDays = null;
        $remainingDays = null;

        if ($registration?->tanggal_mulai && $registration?->tanggal_selesai) {
            $startDate = Carbon::parse($registration->tanggal_mulai)->startOfDay();
            $endDate = Carbon::parse($registration->tanggal_selesai)->startOfDay();
            $effectiveEndDate = $registration->isPostInternshipPhase()
                ? $endDate
                : now()->startOfDay()->min($endDate);

            $totalDays = $startDate->diffInDays($endDate) + 1;
            $completedDays = min(
                $totalDays,
                max(0, $startDate->diffInDays($effectiveEndDate) + 1),
            );
            $remainingDays = max(0, $totalDays - $completedDays);
            $progressPercentage = $totalDays > 0
                ? (int) round(($completedDays / $totalDays) * 100)
                : 0;
        }

        return [$progressPercentage, $totalDays, $completedDays, $remainingDays];
    }

    private function formatLocalizedDate(mixed $value, string $format): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            if ($value instanceof CarbonInterface) {
                return $value->translatedFormat($format);
            }

            return Carbon::parse((string) $value)->translatedFormat($format);
        } catch (\Throwable) {
            return null;
        }
    }

    private function mapLegacyAssessmentStatus(?string $statusKey): ?string
    {
        return match ($statusKey) {
            'submitted' => 'selesai',
            'final_dosen' => 'final_dosen',
            'final_mitra' => 'final_mitra',
            'draft' => 'draft',
            default => null,
        };
    }
}
