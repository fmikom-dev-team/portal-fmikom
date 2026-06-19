<?php

namespace App\Modules\Wims\Services\Mitra;

use App\Models\Magang\AbsensiMagang;
use App\Models\Magang\AssessmentSubmission;
use App\Models\Magang\HariLibur;
use App\Models\Magang\LogbookMagang;
use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use App\Modules\Wims\Services\Shared\Portal\WimsModuleRoleService;
use App\Modules\Wims\Support\AssessmentSummary;
use App\Modules\Wims\Services\Shared\Attendance\AttendanceSyncService;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MitraMonitoringOverviewService
{
    public function __construct(
        private readonly AttendanceSyncService $attendanceSyncService,
        private readonly WimsModuleRoleService $wimsModuleRoleService,
    ) {
    }

    public function buildOverview(User $mentor, ?PerusahaanMitra $company, ?string $today = null): array
    {
        if (! $company) {
            return [
                'students' => collect(),
                'summary' => $this->emptySummary(),
                'attendanceBoard' => [],
                'reviewBoard' => [],
            ];
        }

        $today = $today ?: now()->toDateString();
        $pendaftarans = $this->buildDashboardQuery($mentor, $company)->get();

        $this->attendanceSyncService->syncForRegistrations($pendaftarans);
        $this->wimsModuleRoleService->preloadContextRoles([
            ...$pendaftarans->pluck('perusahaan.user')->filter()->all(),
            ...$pendaftarans->pluck('dosenPembimbing')->filter()->all(),
        ]);

        $students = $pendaftarans
            ->map(function (PendaftaranMagang $pendaftaran) use ($mentor, $today) {
                $assessmentSubmission = AssessmentSummary::latestSubmission(
                    $pendaftaran->assessmentSubmissions,
                    'mitra',
                    $mentor->id,
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
                $attendanceStatus = $phase === 'upcoming'
                    ? 'belum_mulai'
                    : $this->resolveAttendanceStatus($attendance);
                $logbookStatus = $phase === 'upcoming'
                    ? 'belum_mulai'
                    : $this->resolveLogbookStatus($latestLogbook);
                $objectiveSummary = $this->buildObjectiveSummary($pendaftaran, $phase, $today, $assessmentSubmission);

                return [
                    'registration_id' => $pendaftaran->id,
                    'student_id' => $pendaftaran->mahasiswa?->id,
                    'photo_url' => $pendaftaran->mahasiswa?->photoUrl(),
                    'name' => $pendaftaran->mahasiswa?->name,
                    'email' => $pendaftaran->mahasiswa?->email,
                    'nim' => $pendaftaran->mahasiswa?->nim_nip ?: $pendaftaran->mahasiswa?->nomor_induk,
                    'phone' => $pendaftaran->mahasiswa?->no_telepon,
                    'company' => [
                        'id' => $pendaftaran->perusahaan?->id,
                        'name' => $pendaftaran->perusahaan?->nama,
                    ],
                    'mentor' => [
                        'id' => $pendaftaran->perusahaan?->user?->id,
                        'name' => $pendaftaran->perusahaan?->user?->name,
                        'role_context' => $pendaftaran->perusahaan?->user
                            ? $this->wimsModuleRoleService->resolveContextRoleData($pendaftaran->perusahaan->user, 'mitra')
                            : null,
                    ],
                    'lecturer' => [
                        'id' => $pendaftaran->dosenPembimbing?->id,
                        'name' => $pendaftaran->dosenPembimbing?->name,
                        'role_context' => $pendaftaran->dosenPembimbing
                            ? $this->wimsModuleRoleService->resolveContextRoleData($pendaftaran->dosenPembimbing, 'dosen')
                            : null,
                    ],
                    'period_start' => $this->formatDate($pendaftaran->tanggal_mulai),
                    'period_end' => $this->formatDate($pendaftaran->tanggal_selesai),
                    'submitted_at' => $pendaftaran->created_at?->translatedFormat('d M Y H:i'),
                    'status_pendaftaran' => $pendaftaran->status,
                    'dashboard_phase' => $phase,
                    'attendance_status' => $attendanceStatus,
                    'check_in_time' => $attendance?->timestamp_masuk?->format('H:i'),
                    'check_out_time' => $attendance?->timestamp_keluar?->format('H:i'),
                    'latest_logbook_id' => $latestLogbook?->id,
                    'logbook_status' => $logbookStatus,
                    'latest_logbook_date' => $latestLogbook?->tanggal?->translatedFormat('d M Y'),
                    'latest_logbook_sort_date' => $latestLogbook?->tanggal?->toDateString(),
                    'latest_logbook_activity' => str($latestLogbook?->aktivitas_harian ?? '')
                        ->squish()
                        ->limit(110)
                        ->toString(),
                    'latest_logbook_competency' => $latestLogbook?->kompetensi_dicapai,
                    'latest_logbook_note' => $latestLogbook?->catatan_mitra ?? $latestLogbook?->catatan_dosen,
                    'objective_summary' => $objectiveSummary,
                ];
            })
            ->filter(fn (array $student) => $student['student_id'] !== null)
            ->values();

        return [
            'students' => $students,
            'summary' => $this->buildSummary($students),
            'attendanceBoard' => $students
                ->filter(fn (array $student) => $student['dashboard_phase'] === 'active')
                ->map(fn (array $student) => [
                    'registration_id' => $student['registration_id'],
                    'name' => $student['name'],
                    'nim' => $student['nim'],
                    'attendance_status' => $student['attendance_status'],
                    'check_in_time' => $student['check_in_time'],
                    'check_out_time' => $student['check_out_time'],
                ])
                ->values()
                ->all(),
            'reviewBoard' => $students
                ->filter(fn (array $student) => in_array($student['dashboard_phase'], ['active', 'completed'], true))
                ->filter(fn (array $student) => in_array($student['logbook_status'], ['menunggu_review', 'revisi'], true))
                ->sortByDesc(fn (array $student) => $student['latest_logbook_sort_date'] ?? '')
                ->take(6)
                ->values()
                ->all(),
        ];
    }

    private function buildSummary(Collection $students): array
    {
        return [
            'total_students' => $students->count(),
            'upcoming_students' => $students->where('dashboard_phase', 'upcoming')->count(),
            'active_students' => $students->where('dashboard_phase', 'active')->count(),
            'completed_students' => $students->where('dashboard_phase', 'completed')->count(),
            'not_present_today' => $students
                ->where('dashboard_phase', 'active')
                ->filter(fn (array $student) => in_array($student['attendance_status'], ['alfa'], true))
                ->count(),
            'needs_review' => $students
                ->where('dashboard_phase', 'active')
                ->where('logbook_status', 'menunggu_review')
                ->count(),
            'not_evaluated' => $students
                ->where('dashboard_phase', 'completed')
                ->filter(fn (array $student) => ($student['objective_summary']['evaluation_status'] ?? 'not_assessed') !== 'submitted')
                ->count(),
            'pending_absence_requests' => 0,
            'active_warnings' => $students
                ->where('dashboard_phase', 'active')
                ->filter(fn (array $student) => in_array($student['attendance_status'], ['alfa'], true)
                    || in_array($student['logbook_status'], ['menunggu_review', 'revisi'], true))
                ->count(),
        ];
    }

    private function emptySummary(): array
    {
        return [
            'total_students' => 0,
            'upcoming_students' => 0,
            'active_students' => 0,
            'completed_students' => 0,
            'not_present_today' => 0,
            'needs_review' => 0,
            'not_evaluated' => 0,
            'active_warnings' => 0,
            'pending_absence_requests' => 0,
        ];
    }

    private function buildDashboardQuery(User $mentor, PerusahaanMitra $company): Builder
    {
        return PendaftaranMagang::query()
            ->with([
                'mahasiswa',
                'perusahaan',
                'perusahaan.user',
                'dosenPembimbing',
                'assessmentSubmissions' => fn ($query) => AssessmentSummary::orderLatestFirst($query)
                    ->where('assessor_role', 'mitra')
                    ->where('assessor_id', $mentor->id)
                    ->with('template:id,name'),
            ])
            ->whereIn('status', ['approved', 'aktif', 'selesai'])
            ->where('perusahaan_id', $company->id)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id');
    }

    private function resolveDashboardPhase(PendaftaranMagang $pendaftaran, string $today): string
    {
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
            'mentor_evaluation_submitted' => $assessmentSubmission?->status === 'submitted',
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
            'mentor_evaluation_submitted' => $assessmentSubmission?->status === 'submitted',
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
}
