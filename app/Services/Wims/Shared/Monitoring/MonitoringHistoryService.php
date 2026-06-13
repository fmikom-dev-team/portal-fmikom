<?php

namespace App\Services\Wims\Shared\Monitoring;

use App\Models\AbsensiMagang;
use App\Models\HariLibur;
use App\Models\LogbookMagang;
use App\Models\PendaftaranMagang;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;

class MonitoringHistoryService
{
    public function buildAttendanceTimeline(PendaftaranMagang $pendaftaran): array
    {
        if (blank($pendaftaran->tanggal_mulai) || blank($pendaftaran->tanggal_selesai)) {
            return [];
        }

        $startDate = Carbon::parse($pendaftaran->tanggal_mulai)->startOfDay();
        $endDate = Carbon::parse($pendaftaran->tanggal_selesai)->startOfDay();
        $today = now()->startOfDay();

        if ($endDate->greaterThan($today)) {
            $endDate = $today;
        }

        if ($startDate->greaterThan($endDate)) {
            return [];
        }

        $attendanceByDate = AbsensiMagang::query()
            ->where('pendaftaran_id', $pendaftaran->id)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderByDesc('id')
            ->get()
            ->keyBy(fn (AbsensiMagang $attendance) => $attendance->tanggal?->toDateString());

        $holidayDates = HariLibur::query()
            ->where('is_active', true)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->keyBy(fn (HariLibur $holiday) => $holiday->tanggal?->toDateString());

        return collect(CarbonPeriod::create($startDate, $endDate))
            ->reverse()
            ->map(function (Carbon $date) use ($attendanceByDate, $holidayDates, $pendaftaran): array {
                /** @var AbsensiMagang|null $attendance */
                $attendance = $attendanceByDate->get($date->toDateString());
                /** @var HariLibur|null $holiday */
                $holiday = $holidayDates->get($date->toDateString());

                return $this->transformAttendanceHistory($date, $attendance, $pendaftaran, $holiday);
            })
            ->values()
            ->all();
    }

    public function buildLogbookHistory(PendaftaranMagang $pendaftaran, bool $preferDosenNote = false): array
    {
        return LogbookMagang::query()
            ->with('reviewedByMitra')
            ->where('pendaftaran_id', $pendaftaran->id)
            ->latest('tanggal')
            ->latest('id')
            ->limit(20)
            ->get()
            ->map(fn (LogbookMagang $item) => $this->transformLogbookHistory($item, $preferDosenNote))
            ->values()
            ->all();
    }

    public function resolveAttendanceStatus(?AbsensiMagang $attendance): string
    {
        if (! $attendance) {
            return 'alfa';
        }

        if ($attendance->status === 'izin') {
            return 'izin';
        }

        if ($attendance->status === 'sakit') {
            return 'sakit';
        }

        if ($attendance->status === 'alfa') {
            return 'alfa';
        }

        if (! $attendance->timestamp_masuk) {
            return 'alfa';
        }

        if ($attendance->status === 'terlambat') {
            return 'terlambat';
        }

        return 'tepat_waktu';
    }

    private function transformAttendanceHistory(
        Carbon $date,
        ?AbsensiMagang $attendance,
        PendaftaranMagang $pendaftaran,
        ?HariLibur $holiday = null,
    ): array {
        $status = $this->resolveAttendanceTimelineStatus($date, $attendance, $pendaftaran, $holiday);

        return [
            'id' => $attendance?->id ?? $date->toDateString(),
            'tanggal' => $date->toDateString(),
            'tanggal_label' => $date->translatedFormat('d M Y'),
            'check_in' => $attendance?->timestamp_masuk?->toIso8601String(),
            'check_out' => $attendance?->timestamp_keluar?->toIso8601String(),
            'check_in_photo_url' => $attendance?->checkInPhotoUrl(),
            'check_out_photo_url' => $attendance?->checkOutPhotoUrl(),
            'status' => $status,
            'keterangan' => $holiday?->nama,
        ];
    }

    private function transformLogbookHistory(LogbookMagang $logbook, bool $preferDosenNote): array
    {
        return [
            'id' => $logbook->id,
            'tanggal' => $logbook->tanggal?->toDateString(),
            'tanggal_label' => $logbook->tanggal?->translatedFormat('d M Y'),
            'aktivitas' => $logbook->aktivitas_harian,
            'kompetensi' => $logbook->kompetensi_dicapai,
            'status' => $logbook->status,
            'catatan_mitra' => $preferDosenNote
                ? ($logbook->catatan_mitra ?? $logbook->catatan_dosen)
                : $logbook->catatan_mitra,
            'reviewed_by_name' => $logbook->reviewedByMitra?->name,
            'reviewed_at' => $logbook->reviewed_by_mitra_at?->toIso8601String(),
        ];
    }

    private function resolveAttendanceTimelineStatus(
        Carbon $date,
        ?AbsensiMagang $attendance,
        PendaftaranMagang $pendaftaran,
        ?HariLibur $holiday = null,
    ): string {
        if ($attendance && in_array($attendance->status, ['izin', 'sakit', 'alfa'], true)) {
            return $attendance->status;
        }

        if ($attendance?->timestamp_masuk) {
            return $this->resolveAttendanceStatus($attendance);
        }

        if ($holiday) {
            return 'hari_libur';
        }

        if (! $pendaftaran->perusahaan?->worksOnDate($date)) {
            return 'bukan_hari_kerja';
        }

        return 'alfa';
    }
}
