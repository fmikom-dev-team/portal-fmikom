<?php

namespace App\Modules\Wims\Services\Shared\Attendance;

use App\Models\Magang\AbsensiMagang;
use App\Models\Magang\HariLibur;
use App\Models\Magang\KetidakhadiranMagang;
use App\Models\Magang\PendaftaranMagang;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AttendanceSyncService
{
    public function syncForRegistrations(iterable $registrations, ?CarbonInterface $referenceDate = null): void
    {
        foreach ($registrations as $registration) {
            if ($registration instanceof PendaftaranMagang) {
                $this->syncForRegistration($registration, $referenceDate);
            }
        }
    }

    public function syncForRegistration(PendaftaranMagang $registration, ?CarbonInterface $referenceDate = null): void
    {
        $registration->loadMissing('perusahaan');

        if (
            blank($registration->tanggal_mulai)
            || blank($registration->tanggal_selesai)
            || ! $registration->perusahaan
        ) {
            return;
        }

        $startDate = Carbon::parse($registration->tanggal_mulai)->startOfDay();
        $endDate = Carbon::parse($registration->tanggal_selesai)->startOfDay();
        $syncUntil = Carbon::parse($referenceDate ?? now())->startOfDay()->subDay();

        if ($endDate->lessThan($syncUntil)) {
            $syncUntil = $endDate;
        }

        if ($syncUntil->lessThan($startDate)) {
            return;
        }

        $holidayDates = $this->getHolidayLookup($startDate, $syncUntil);
        $workDates = collect(CarbonPeriod::create($startDate, $syncUntil))
            ->filter(fn (Carbon $date) => $registration->perusahaan->worksOnDate($date, $holidayDates))
            ->map(fn (Carbon $date) => $date->toDateString())
            ->values();

        if ($workDates->isEmpty()) {
            return;
        }

        $approvedAbsences = KetidakhadiranMagang::query()
            ->where('pendaftaran_id', $registration->id)
            ->where('status', 'approved')
            ->whereDate('tanggal_mulai', '<=', $syncUntil->toDateString())
            ->whereDate('tanggal_selesai', '>=', $startDate->toDateString())
            ->orderByDesc('id')
            ->get();

        foreach ($workDates as $date) {
            $attendance = AbsensiMagang::query()
                ->where('pendaftaran_id', $registration->id)
                ->whereDate('tanggal', $date)
                ->latest('id')
                ->first();

            if ($this->isManualAttendanceLocked($attendance)) {
                continue;
            }

            /** @var KetidakhadiranMagang|null $approvedAbsence */
            $approvedAbsence = $approvedAbsences->first(
                fn (KetidakhadiranMagang $absence) => $absence->tanggal_mulai?->toDateString() <= $date
                    && $absence->tanggal_selesai?->toDateString() >= $date,
            );

            $attendance ??= new AbsensiMagang([
                'pendaftaran_id' => $registration->id,
                'tanggal' => $date,
            ]);

            $attendance->fill(
                $approvedAbsence
                    ? $this->buildExcusedPayload($approvedAbsence)
                    : $this->buildAlphaPayload()
            );
            $attendance->save();
        }
    }

    public function isManualAttendanceLocked(?AbsensiMagang $attendance): bool
    {
        if (! $attendance) {
            return false;
        }

        if (filled($attendance->timestamp_masuk) || filled($attendance->waktu_masuk)) {
            return true;
        }

        if (filled($attendance->timestamp_keluar) || filled($attendance->waktu_keluar)) {
            return true;
        }

        if (filled($attendance->foto_bukti_path) || filled($attendance->foto_bukti_checkout_path)) {
            return true;
        }

        if (filled($attendance->latitude_masuk) || filled($attendance->longitude_masuk)) {
            return true;
        }

        return in_array($attendance->status, ['hadir', 'terlambat'], true);
    }

    private function buildExcusedPayload(KetidakhadiranMagang $absence): array
    {
        return [
            'status' => $absence->jenis === 'sakit' ? 'sakit' : 'izin',
            'keterangan' => $absence->alasan,
            'lokasi_valid' => false,
            'waktu_masuk' => null,
            'waktu_keluar' => null,
            'timestamp_masuk' => null,
            'timestamp_keluar' => null,
            'latitude_masuk' => null,
            'longitude_masuk' => null,
            'latitude_keluar' => null,
            'longitude_keluar' => null,
            'distance_masuk' => null,
            'distance_keluar' => null,
            'foto_bukti_path' => null,
            'foto_bukti_checkout_path' => null,
            'ip_address' => null,
            'user_agent' => null,
        ];
    }

    private function buildAlphaPayload(): array
    {
        return [
            'status' => 'alfa',
            'keterangan' => 'Tidak hadir tanpa presensi masuk.',
            'lokasi_valid' => false,
            'waktu_masuk' => null,
            'waktu_keluar' => null,
            'timestamp_masuk' => null,
            'timestamp_keluar' => null,
            'latitude_masuk' => null,
            'longitude_masuk' => null,
            'latitude_keluar' => null,
            'longitude_keluar' => null,
            'distance_masuk' => null,
            'distance_keluar' => null,
            'foto_bukti_path' => null,
            'foto_bukti_checkout_path' => null,
            'ip_address' => null,
            'user_agent' => null,
        ];
    }

    private function getHolidayLookup(CarbonInterface $startDate, CarbonInterface $endDate): Collection
    {
        return HariLibur::query()
            ->where('is_active', true)
            ->whereBetween('tanggal', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('tanggal')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->flip();
    }
}
