<?php

namespace App\Services;

use App\Models\AbsensiMagang;
use App\Models\HariLibur;
use App\Models\LogbookMagang;
use App\Models\PendaftaranMagang;
use App\Models\PerusahaanMitra;
use App\Models\User;
use Carbon\CarbonPeriod;
use Carbon\CarbonInterface;
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

        $registrations = $this->buildBaseQuery($user, $referenceDate)->get();
        $this->attendanceSyncService->syncForRegistrations($registrations, $referenceDate);

        return $registrations
            ->map(fn (PendaftaranMagang $pendaftaran) => $this->buildWarningItem($pendaftaran, $referenceDate, $threshold))
            ->filter()
            ->values();
    }

    public function getWarningsForActiveInternships(int $threshold = 3, ?CarbonInterface $referenceDate = null): Collection
    {
        $referenceDate ??= now()->startOfDay();

        $registrations = $this->buildBaseQuery(null, $referenceDate)->get();
        $this->attendanceSyncService->syncForRegistrations($registrations, $referenceDate);

        return $registrations
            ->map(fn (PendaftaranMagang $pendaftaran) => $this->buildWarningItem($pendaftaran, $referenceDate, $threshold))
            ->filter()
            ->values();
    }

    public function getWarningsForCompany(PerusahaanMitra $company, int $threshold = 3, ?CarbonInterface $referenceDate = null): Collection
    {
        $referenceDate ??= now()->startOfDay();

        $registrations = $this->buildBaseQuery(null, $referenceDate)
            ->where('perusahaan_id', $company->id)
            ->get();
        $this->attendanceSyncService->syncForRegistrations($registrations, $referenceDate);

        return $registrations
            ->map(fn (PendaftaranMagang $pendaftaran) => $this->buildWarningItem($pendaftaran, $referenceDate, $threshold))
            ->filter()
            ->values();
    }

    private function buildBaseQuery(?User $user, CarbonInterface $referenceDate): Builder
    {
        return PendaftaranMagang::query()
            ->with(['mahasiswa', 'perusahaan'])
            ->where('status', 'aktif')
            ->whereDate('tanggal_mulai', '<=', $referenceDate->toDateString())
            ->whereDate('tanggal_selesai', '>=', $referenceDate->toDateString())
            ->when(
                $user && ! $user->hasRole('super-admin'),
                fn (Builder $query) => $query->where('dosen_pembimbing_id', $user->id),
            )
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id');
    }

    private function buildWarningItem(PendaftaranMagang $pendaftaran, CarbonInterface $referenceDate, int $threshold): ?array
    {
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

        $logbookDates = LogbookMagang::query()
            ->where('pendaftaran_id', $pendaftaran->id)
            ->whereBetween('tanggal', [
                $periodDates->last()?->toDateString(),
                $periodDates->first()?->toDateString(),
            ])
            ->pluck('tanggal')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->flip();

        $attendanceMissingDays = $this->countAttendanceMissingStreak($periodDates, $attendanceRows);
        $logbookMissingDays = $this->countLogbookMissingStreak($periodDates, $logbookDates, $attendanceRows);

        // Warning hanya muncul jika streak ketidakhadiran atau logbook kosong melewati ambang monitoring.
        if ($attendanceMissingDays < $threshold && $logbookMissingDays < $threshold) {
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
            'missing_types' => array_values(array_filter([
                $attendanceMissingDays >= $threshold ? 'absensi' : null,
                $logbookMissingDays >= $threshold ? 'logbook' : null,
            ])),
            'last_monitoring_date' => $referenceDate->translatedFormat('d M Y'),
        ];
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

        // Monitoring hanya menghitung hari kerja efektif perusahaan, bukan seluruh hari kalender.
        return collect(CarbonPeriod::create($startDate, $endDate))
            ->reject(fn (CarbonInterface $date) => ! $pendaftaran->perusahaan?->worksOnDate($date, $holidayDates))
            ->reverse()
            ->values();
    }

    private function countLogbookMissingStreak(
        Collection $dates,
        Collection $existingDates,
        Collection $attendanceRows,
    ): int
    {
        $count = 0;

        foreach ($dates as $date) {
            /** @var AbsensiMagang|null $attendance */
            $attendance = $attendanceRows->get($date->toDateString());

            // Izin/sakit approved should pause the streak instead of resetting or increasing it.
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
            /** @var AbsensiMagang|null $attendance */
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

    private function isValidAttendanceDay(?AbsensiMagang $attendance): bool
    {
        if (! $attendance) {
            return false;
        }

        if (filled($attendance->timestamp_masuk)) {
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
