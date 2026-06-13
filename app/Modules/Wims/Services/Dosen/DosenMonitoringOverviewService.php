<?php

namespace App\Modules\Wims\Services\Dosen;

use App\Models\Magang\AbsensiMagang;
use App\Models\AssessmentSubmission;
use App\Models\HariLibur;
use App\Models\Magang\LogbookMagang;
use App\Models\Magang\PendaftaranMagang;
use App\Models\User;
use App\Services\AttendanceSyncService;
use App\Modules\Wims\Support\AssessmentSummary;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DosenMonitoringOverviewService
{
    public function __construct(
        private readonly AttendanceSyncService $attendanceSyncService,
    ) {
    }

    public function buildOverview(User $lecturer, ?string $today = null): array
    {
        $today = $today ?: now()->toDateString();
        $pendaftarans = $this->buildDashboardQuery($lecturer)->get();
        $this->attendanceSyncService->syncForRegistrations($pendaftarans);

        $students = $pendaftarans
            ->map(function (PendaftaranMagang $pendaftaran) use ($today) {
                $assessmentSubmission = AssessmentSummary::latestSubmission(
                    $pendaftaran->assessmentSubmissions,
                    'dosen',
                    $pendaftaran->dosen_pembimbing_id,
                );
                $phase = $this->resolveDashboardPhase($pendaftaran, $today);
                $referenceDate = $this->resolveDashboardReferenceDate($pendaftaran, $today);
                $attendance = AbsensiMagang::query()
                    ->where('pendaftaran_id', $pendaftaran->id)
                    ->whereDate('tanggal', $referenceDate)
                    ->latest('id')
                    ->first();
                $latestLogbook = LogbookMagang::query()
                    ->where('pendaftaran_id', $pendaftaran->id)
                    ->latest('tanggal')
                    ->latest('id')
                    ->first();

                return [
                    'id' => $pendaftaran->mahasiswa?->id,
                    'pendaftaran_id' => $pendaftaran->id,
                    'name' => $pendaftaran->mahasiswa?->name,
                    'nim' => $pendaftaran->mahasiswa?->nim_nip ?: $pendaftaran->mahasiswa?->nomor_induk,
                    'company' => $pendaftaran->perusahaan?->nama,
                    'period_start' => $this->formatDate($pendaftaran->tanggal_mulai),
                    'period_end' => $this->formatDate($pendaftaran->tanggal_selesai),
                    'status_pendaftaran' => $pendaftaran->status,
                    'dashboard_phase' => $phase,
                    'attendance_status' => $phase === 'upcoming'
                        ? 'belum_mulai'
                        : $this->resolveAttendanceStatus($attendance),
                    'check_in_time' => $attendance?->timestamp_masuk?->translatedFormat('H:i'),
                    'check_out_time' => $attendance?->timestamp_keluar?->translatedFormat('H:i'),
                    'logbook_status' => $phase === 'upcoming'
                        ? 'belum_mulai'
                        : $this->resolveLogbookStatus($latestLogbook),
                    'has_logbook' => $latestLogbook !== null,
                    'latest_logbook_date' => $latestLogbook?->tanggal?->translatedFormat('d M Y'),
                    'latest_logbook_activity' => $this->summarizeText($latestLogbook?->aktivitas_harian),
                    'today_date' => $referenceDate,
                    'objective_summary' => $this->buildObjectiveSummary($pendaftaran, $phase, $today, $assessmentSubmission),
                ];
            })
            ->filter(fn (array $student) => $student['id'] !== null)
            ->values();

        return [
            'students' => $students,
            'summary' => $this->buildSummary($students),
        ];
    }

    private function buildSummary(Collection $students): array
    {
        return [
            'total_students' => $students->count(),
            'upcoming_students' => $students->where('dashboard_phase', 'upcoming')->count(),
            'active_students' => $students->where('dashboard_phase', 'active')->count(),
            'completed_students' => $students->where('dashboard_phase', 'completed')->count(),
            'needs_review' => $students
                ->where('dashboard_phase', 'active')
                ->filter(fn (array $student) => in_array($student['logbook_status'], ['belum_isi', 'revisi'], true))
                ->count(),
            'not_present' => $students
                ->where('dashboard_phase', 'active')
                ->where('attendance_status', 'alfa')
                ->count(),
            'active_warnings' => $students
                ->where('dashboard_phase', 'active')
                ->filter(fn (array $student) => $student['attendance_status'] === 'alfa'
                    || in_array($student['logbook_status'], ['belum_isi', 'revisi'], true))
                ->count(),
            'not_evaluated' => $students
                ->where('dashboard_phase', 'completed')
                ->filter(fn (array $student) => ($student['objective_summary']['evaluation_status'] ?? 'not_assessed') !== 'submitted')
                ->count(),
        ];
    }

    private function buildDashboardQuery(User $lecturer): Builder
    {
        return PendaftaranMagang::query()
            ->with([
                'mahasiswa',
                'perusahaan',
                'assessmentSubmissions' => fn ($query) => AssessmentSummary::orderLatestFirst($query)
                    ->where('assessor_id', $lecturer->id)
                    ->where('assessor_role', 'dosen'),
            ])
            ->when(
                ! $lecturer->hasRole('super-admin'),
                fn (Builder $query) => $query->where('dosen_pembimbing_id', $lecturer->id),
            )
            ->whereIn('status', ['approved', 'aktif', 'selesai'])
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id');
    }

    private function resolveDashboardPhase(PendaftaranMagang $pendaftaran, string $today): string
    {
        // Phase dashboard dipakai UI untuk membedakan mahasiswa yang belum mulai,
        // sedang aktif, atau sudah masuk fase evaluasi.
        if ($pendaftaran->isReadyForAssessment(Carbon::parse($today))) {
            return 'completed';
        }

        if (blank($pendaftaran->tanggal_mulai) || blank($pendaftaran->tanggal_selesai)) {
            return 'assigned';
        }

        $todayDate = Carbon::parse($today)->startOfDay();
        $startDate = Carbon::parse($pendaftaran->tanggal_mulai)->startOfDay();

        if ($startDate->greaterThan($todayDate)) {
            return 'upcoming';
        }

        if ($pendaftaran->status === 'approved') {
            return 'assigned';
        }

        if ($pendaftaran->status === 'aktif') {
            return 'active';
        }

        return 'assigned';
    }

    private function resolveDashboardReferenceDate(PendaftaranMagang $pendaftaran, string $today): string
    {
        if (blank($pendaftaran->tanggal_selesai)) {
            return $today;
        }

        $todayDate = Carbon::parse($today)->startOfDay();
        $endDate = Carbon::parse($pendaftaran->tanggal_selesai)->startOfDay();

        return $endDate->lessThan($todayDate)
            ? $endDate->toDateString()
            : $todayDate->toDateString();
    }

    private function buildObjectiveSummary(
        PendaftaranMagang $pendaftaran,
        string $phase,
        string $today,
        ?AssessmentSubmission $assessmentSubmission = null,
    ): array {
        $emptySummary = [
            'expected_workdays' => 0,
            'attendance_total' => 0,
            'attendance_late' => 0,
            'attendance_rate' => 0,
            'logbook_total' => 0,
            'logbook_approved' => 0,
            'logbook_revision' => 0,
            'logbook_pending' => 0,
            'logbook_rate' => 0,
            'final_report_uploaded' => filled($pendaftaran->laporan_akhir_path),
            'evaluation_status' => $assessmentSubmission?->status ?? 'not_assessed',
            'evaluation_total_score' => $assessmentSubmission?->total_score,
        ];

        if (
            $phase === 'upcoming'
            || blank($pendaftaran->tanggal_mulai)
            || blank($pendaftaran->tanggal_selesai)
        ) {
            return $emptySummary;
        }

        $startDate = Carbon::parse($pendaftaran->tanggal_mulai)->startOfDay();
        $endDate = Carbon::parse($pendaftaran->tanggal_selesai)->startOfDay();
        $todayDate = Carbon::parse($today)->startOfDay();
        $referenceEndDate = $endDate->lessThan($todayDate) ? $endDate : $todayDate;

        if ($startDate->greaterThan($referenceEndDate)) {
            return $emptySummary;
        }

        $holidayDates = HariLibur::query()
            ->where('is_active', true)
            ->whereBetween('tanggal', [$startDate->toDateString(), $referenceEndDate->toDateString()])
            ->pluck('tanggal')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->flip();

        // Expected workdays menjadi baseline objektif untuk rasio absensi dan logbook mahasiswa.
        $expectedWorkdays = collect(CarbonPeriod::create($startDate, $referenceEndDate))
            ->filter(fn (Carbon $date) => $pendaftaran->perusahaan?->worksOnDate($date, $holidayDates))
            ->count();

        $attendanceRows = AbsensiMagang::query()
            ->where('pendaftaran_id', $pendaftaran->id)
            ->whereBetween('tanggal', [$startDate->toDateString(), $referenceEndDate->toDateString()])
            ->get();

        $logbookRows = LogbookMagang::query()
            ->where('pendaftaran_id', $pendaftaran->id)
            ->whereBetween('tanggal', [$startDate->toDateString(), $referenceEndDate->toDateString()])
            ->get();

        $attendanceTotal = $attendanceRows->count();
        $attendanceLate = $attendanceRows->where('status', 'terlambat')->count();
        $logbookTotal = $logbookRows->count();
        $logbookApproved = $logbookRows->filter(
            fn (LogbookMagang $logbook) => in_array($logbook->status, ['approved', 'disetujui'], true),
        )->count();
        $logbookRevision = $logbookRows->filter(
            fn (LogbookMagang $logbook) => in_array($logbook->status, ['rejected', 'revisi'], true),
        )->count();
        $logbookPending = $logbookRows->where('status', 'pending')->count();

        return [
            'expected_workdays' => $expectedWorkdays,
            'attendance_total' => $attendanceTotal,
            'attendance_late' => $attendanceLate,
            'attendance_rate' => $expectedWorkdays > 0
                ? (int) round(($attendanceTotal / $expectedWorkdays) * 100)
                : 0,
            'logbook_total' => $logbookTotal,
            'logbook_approved' => $logbookApproved,
            'logbook_revision' => $logbookRevision,
            'logbook_pending' => $logbookPending,
            'logbook_rate' => $expectedWorkdays > 0
                ? (int) round(($logbookTotal / $expectedWorkdays) * 100)
                : 0,
            'final_report_uploaded' => filled($pendaftaran->laporan_akhir_path),
            'evaluation_status' => $assessmentSubmission?->status ?? 'not_assessed',
            'evaluation_total_score' => $assessmentSubmission?->total_score,
        ];
    }

    private function resolveAttendanceStatus(?AbsensiMagang $attendance): string
    {
        if (! $attendance) {
            return 'alfa';
        }

        if (in_array($attendance->status, ['izin', 'sakit'], true)) {
            return $attendance->status;
        }

        if ($attendance->status === 'alfa') {
            return 'alfa';
        }

        if (! $attendance?->timestamp_masuk) {
            return 'alfa';
        }

        if ($attendance->status === 'terlambat') {
            return 'terlambat';
        }

        return 'hadir';
    }

    private function resolveLogbookStatus(?LogbookMagang $logbook): string
    {
        if (! $logbook) {
            return 'belum_isi';
        }

        if (in_array($logbook->status, ['approved', 'disetujui'], true)) {
            return 'disetujui';
        }

        if (in_array($logbook->status, ['rejected', 'revisi'], true)) {
            return 'revisi';
        }

        return 'menunggu_review';
    }

    private function formatDate(mixed $date): ?string
    {
        if (blank($date)) {
            return null;
        }

        if ($date instanceof Carbon) {
            return $date->translatedFormat('d M Y');
        }

        return Carbon::parse($date)->translatedFormat('d M Y');
    }

    private function summarizeText(?string $value, int $limit = 110): ?string
    {
        if (blank($value)) {
            return null;
        }

        return str($value)->squish()->limit($limit)->value();
    }
}
