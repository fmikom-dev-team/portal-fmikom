<?php

namespace App\Modules\Wims\Services\Shared\Monitoring;

use App\Models\Magang\AbsensiMagang;
use App\Models\Magang\AssessmentSubmission;
use App\Models\Magang\HariLibur;
use App\Models\Magang\LogbookMagang;
use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use App\Modules\Wims\Services\Shared\Attendance\AttendanceSyncService;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MonitoringAlertService
{
    public function __construct(
        private readonly AttendanceSyncService $attendanceSyncService,
    ) {
    }

    public function getWarningsForLecturer(User $user, int $threshold = 3, ?CarbonInterface $referenceDate = null): Collection
    {
        $referenceDate ??= now()->startOfDay();

        $registrations = $this->buildBaseQuery($referenceDate)
            ->where('dosen_pembimbing_id', $user->id)
            ->get();

        return $this->buildWarnings($registrations, $referenceDate, $threshold, 'dosen', $user->id);
    }

    public function getWarningsForActiveInternships(int $threshold = 3, ?CarbonInterface $referenceDate = null): Collection
    {
        $referenceDate ??= now()->startOfDay();

        return $this->buildWarnings(
            $this->buildBaseQuery($referenceDate)->get(),
            $referenceDate,
            $threshold,
            null,
            null,
        );
    }

    public function getWarningsForCompany(PerusahaanMitra $company, int $threshold = 3, ?CarbonInterface $referenceDate = null): Collection
    {
        $referenceDate ??= now()->startOfDay();

        $registrations = $this->buildBaseQuery($referenceDate)
            ->where('perusahaan_id', $company->id)
            ->get();

        return $this->buildWarnings($registrations, $referenceDate, $threshold, 'mitra', $company->user_id);
    }

    private function buildWarnings(
        Collection $registrations,
        CarbonInterface $referenceDate,
        int $threshold,
        ?string $assessmentRole,
        ?int $assessorId,
    ): Collection {
        $this->attendanceSyncService->syncForRegistrations($registrations, $referenceDate);

        return $registrations
            ->map(fn (PendaftaranMagang $pendaftaran) => $this->buildWarningItem(
                $pendaftaran,
                $referenceDate,
                $threshold,
                $assessmentRole,
                $assessorId,
            ))
            ->filter()
            ->values();
    }

    private function buildBaseQuery(CarbonInterface $referenceDate): Builder
    {
        return PendaftaranMagang::query()
            ->with(['mahasiswa', 'perusahaan'])
            ->where('status', 'aktif')
            ->whereDate('tanggal_mulai', '<=', $referenceDate->toDateString())
            ->whereDate('tanggal_selesai', '>=', $referenceDate->toDateString())
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id');
    }

    private function buildWarningItem(
        PendaftaranMagang $pendaftaran,
        CarbonInterface $referenceDate,
        int $threshold,
        ?string $assessmentRole,
        ?int $assessorId,
    ): ?array {
        $periodDates = $this->generateMonitoringDates($pendaftaran, $referenceDate);

        if ($periodDates->isEmpty()) {
            return null;
        }

        $attendanceRows = AbsensiMagang::query()
            ->where('pendaftaran_id', $pendaftaran->id)
            ->whereBetween('tanggal', [
                $periodDates->last()?->toDateString(),
                $periodDates->first()?->toDateString(),
            ])
            ->orderByDesc('id')
            ->get()
            ->keyBy(fn (AbsensiMagang $attendance) => $attendance->tanggal?->toDateString());

        $logbooks = LogbookMagang::query()
            ->where('pendaftaran_id', $pendaftaran->id)
            ->whereBetween('tanggal', [
                $periodDates->last()?->toDateString(),
                $periodDates->first()?->toDateString(),
            ])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $logbookDates = $logbooks
            ->pluck('tanggal')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->flip();

        $attendanceMissingDays = $this->countAttendanceMissingStreak($periodDates, $attendanceRows);
        $logbookMissingDays = $this->countLogbookMissingStreak($periodDates, $logbookDates, $attendanceRows);
        $latestLogbook = $logbooks->first();
        $needsRevision = $latestLogbook !== null && in_array($latestLogbook->status, ['rejected', 'revisi'], true);
        $assessmentPending = $this->isAssessmentPending($pendaftaran, $assessmentRole, $assessorId);
        $finalReportMissing = $pendaftaran->isPostInternshipPhase($referenceDate) && blank($pendaftaran->laporan_akhir_path);

        $missingTypes = array_values(array_filter([
            $attendanceMissingDays >= $threshold ? 'belum_presensi' : null,
            $attendanceMissingDays >= $threshold && $this->latestAttendanceIsAlpha($attendanceRows) ? 'alfa' : null,
            $logbookMissingDays >= $threshold ? 'logbook_belum_diisi' : null,
            $needsRevision ? 'logbook_perlu_revisi' : null,
            $assessmentPending ? 'assessment_belum_dinilai' : null,
            $finalReportMissing ? 'laporan_akhir_belum_tersedia' : null,
        ]));

        if ($missingTypes === []) {
            return null;
        }

        return [
            'student_id' => $pendaftaran->mahasiswa?->id,
            'pendaftaran_id' => $pendaftaran->id,
            'name' => $pendaftaran->mahasiswa?->name,
            'nim' => $pendaftaran->mahasiswa?->nim_nip ?: $pendaftaran->mahasiswa?->nomor_induk,
            'company' => $pendaftaran->perusahaan?->nama,
            'attendance_missing_days' => $attendanceMissingDays,
            'logbook_missing_days' => $logbookMissingDays,
            'latest_logbook_status' => $latestLogbook?->status,
            'assessment_pending' => $assessmentPending,
            'final_report_uploaded' => filled($pendaftaran->laporan_akhir_path),
            'missing_types' => $missingTypes,
            'last_monitoring_date' => $referenceDate->translatedFormat('d M Y'),
        ];
    }

    private function isAssessmentPending(PendaftaranMagang $pendaftaran, ?string $role, ?int $assessorId): bool
    {
        if (! $pendaftaran->isReadyForAssessment()) {
            return false;
        }

        $query = AssessmentSubmission::query()
            ->where('pendaftaran_magang_id', $pendaftaran->id)
            ->where('status', 'submitted');

        if ($role !== null) {
            $query->where('assessor_role', $role);
        }

        if ($assessorId !== null) {
            $query->where('assessor_id', $assessorId);
        }

        return ! $query->exists();
    }

    private function generateMonitoringDates(PendaftaranMagang $pendaftaran, CarbonInterface $referenceDate): Collection
    {
        if (blank($pendaftaran->tanggal_mulai) || blank($pendaftaran->tanggal_selesai)) {
            return collect();
        }

        $startDate = Carbon::parse($pendaftaran->tanggal_mulai)->startOfDay();
        $endDate = Carbon::parse($pendaftaran->tanggal_selesai)->startOfDay();

        if ($endDate->greaterThan($referenceDate)) {
            $endDate = $referenceDate->copy();
        }

        if ($startDate->greaterThan($endDate)) {
            return collect();
        }

        $holidayDates = HariLibur::query()
            ->where('is_active', true)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('tanggal')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->flip();

        return collect(CarbonPeriod::create($startDate, $endDate))
            ->reject(fn (CarbonInterface $date) => ! $pendaftaran->perusahaan?->worksOnDate($date, $holidayDates))
            ->reverse()
            ->values();
    }

    private function countLogbookMissingStreak(
        Collection $dates,
        Collection $existingDates,
        Collection $attendanceRows,
    ): int {
        $count = 0;

        foreach ($dates as $date) {
            $attendance = $attendanceRows->get($date->toDateString());

            if ($this->isExcusedAttendanceDay($attendance)) {
                continue;
            }

            if ($existingDates->has($date->toDateString())) {
                break;
            }

            $count++;
        }

        return $count;
    }

    private function countAttendanceMissingStreak(Collection $dates, Collection $attendanceRows): int
    {
        $count = 0;

        foreach ($dates as $date) {
            $attendance = $attendanceRows->get($date->toDateString());

            if ($this->isExcusedAttendanceDay($attendance)) {
                continue;
            }

            if ($this->isValidAttendanceDay($attendance)) {
                break;
            }

            $count++;
        }

        return $count;
    }

    private function latestAttendanceIsAlpha(Collection $attendanceRows): bool
    {
        $latest = $attendanceRows->first();

        return $latest?->status === 'alfa';
    }

    private function isValidAttendanceDay(?AbsensiMagang $attendance): bool
    {
        if (! $attendance) {
            return false;
        }

        if (filled($attendance->timestamp_masuk) || filled($attendance->waktu_masuk)) {
            return true;
        }

        return in_array($attendance->status, ['hadir', 'terlambat'], true);
    }

    private function isExcusedAttendanceDay(?AbsensiMagang $attendance): bool
    {
        if (! $attendance) {
            return false;
        }

        return in_array($attendance->status, ['izin', 'sakit'], true);
    }
}
