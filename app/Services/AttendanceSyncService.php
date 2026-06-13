<?php

namespace App\Services;

use App\Models\AbsensiMagang;
use App\Models\HariLibur;
use App\Models\KetidakhadiranMagang;
use App\Models\PendaftaranMagang;
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

        $holidayDates = HariLibur::query()
            ->where('is_active', true)
            ->whereBetween('tanggal', [$startDate->toDateString(), $syncUntil->toDateString()])
            ->pluck('tanggal')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->flip();

        $workDates = collect(CarbonPeriod::create($startDate, $syncUntil))
            ->filter(fn (Carbon $date) => $registration->perusahaan?->worksOnDate($date, $holidayDates))
            ->map(fn (Carbon $date) => $date->toDateString())
            ->values();

        if ($workDates->isEmpty()) {
            return;
        }

        $attendanceByDate = AbsensiMagang::query()
            ->where('pendaftaran_id', $registration->id)
            ->whereIn('tanggal', $workDates->all())
            ->orderByDesc('id')
            ->get()
            ->keyBy(fn (AbsensiMagang $attendance) => $attendance->tanggal?->toDateString());

        $approvedAbsences = KetidakhadiranMagang::query()
            ->where('pendaftaran_id', $registration->id)
            ->where('status', 'approved')
            ->whereDate('tanggal_mulai', '<=', $syncUntil->toDateString())
            ->whereDate('tanggal_selesai', '>=', $startDate->toDateString())
            ->orderByDesc('id')
            ->get();

        foreach ($workDates as $date) {
            /** @var AbsensiMagang|null $attendance */
            $attendance = $attendanceByDate->get($date);

            if (filled($attendance?->timestamp_masuk)) {
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
            'keterangan' => 'Tidak hadir tanpa check-in.',
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
}
