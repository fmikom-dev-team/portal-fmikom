<?php

namespace App\Modules\Wims\Services\Mahasiswa\Attendance;

use App\Models\Magang\AbsensiMagang;
use App\Models\Magang\PendaftaranMagang;
use App\Modules\Wims\Services\Shared\Attendance\AttendanceService;
use App\Support\WimsStorage;
use Carbon\CarbonInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class AttendanceActionService
{
    public function __construct(
        private readonly AttendanceService $attendanceService,
        private readonly AttendanceAvailabilityService $attendanceAvailabilityService,
    ) {
    }

    public function validateCheckInAvailability(PendaftaranMagang $pendaftaran): array
    {
        return $this->attendanceAvailabilityService->resolveAvailability($pendaftaran);
    }

    public function validateCheckOutAvailability(PendaftaranMagang $pendaftaran): array
    {
        return $this->attendanceAvailabilityService->resolveAvailability($pendaftaran);
    }

    public function validateLocation(PendaftaranMagang $pendaftaran, float $latitude, float $longitude): array
    {
        $perusahaan = $pendaftaran->perusahaan;

        // Validasi lokasi dipusatkan di service agar mekanisme geofencing pada
        // web-based internship management and attendance system tetap seragam.
        return $this->attendanceService->validateLocation(
            $latitude,
            $longitude,
            $perusahaan->latitude,
            $perusahaan->longitude,
            $perusahaan->radius_valid_meter
        );
    }

    public function hasCheckedInToday(PendaftaranMagang $pendaftaran): bool
    {
        return AbsensiMagang::query()
            ->where('pendaftaran_id', $pendaftaran->id)
            ->where('tanggal', now()->toDateString())
            ->exists();
    }

    public function findTodayAttendance(PendaftaranMagang $pendaftaran): ?AbsensiMagang
    {
        return AbsensiMagang::query()
            ->where('pendaftaran_id', $pendaftaran->id)
            ->whereDate('tanggal', now()->toDateString())
            ->first();
    }

    public function createCheckIn(
        PendaftaranMagang $pendaftaran,
        float $latitude,
        float $longitude,
        ?UploadedFile $photo,
        string $ipAddress,
        ?string $userAgent,
        array $locationResult,
        ?CarbonInterface $checkedAt = null,
    ): AbsensiMagang {
        $checkedAt ??= now();
        $photoPath = $this->storePhoto($photo, 'absensi/check-in');

        return AbsensiMagang::create([
            'pendaftaran_id' => $pendaftaran->id,
            'tanggal' => $checkedAt->toDateString(),
            'waktu_masuk' => $checkedAt->format('H:i:s'),
            'timestamp_masuk' => $checkedAt,
            'latitude_masuk' => $latitude,
            'longitude_masuk' => $longitude,
            // Distance dan status valid merekam hasil validasi geofencing saat check-in.
            'distance_masuk' => $locationResult['distance'],
            'lokasi_valid' => $locationResult['is_valid'],
            'foto_bukti_path' => $photoPath,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'status' => $this->attendanceAvailabilityService->resolveStatus(
                $checkedAt,
                $pendaftaran->perusahaan?->jam_masuk,
                $pendaftaran->perusahaan?->toleransi_terlambat_menit,
            ),
        ]);
    }

    public function completeCheckOut(
        AbsensiMagang $attendance,
        float $latitude,
        float $longitude,
        ?UploadedFile $photo,
        array $locationResult,
        ?CarbonInterface $checkedOutAt = null,
    ): void {
        $checkedOutAt ??= now();
        $photoPath = $this->storePhoto($photo, 'absensi/check-out');

        $attendance->update([
            'timestamp_keluar' => $checkedOutAt,
            'waktu_keluar' => $checkedOutAt->format('H:i:s'),
            'latitude_keluar' => $latitude,
            'longitude_keluar' => $longitude,
            // Check-out menyimpan ulang hasil validasi lokasi agar bukti kehadiran
            // saat masuk dan keluar dapat dianalisis terpisah.
            'distance_keluar' => $locationResult['distance'],
            'foto_bukti_checkout_path' => $photoPath,
        ]);
    }

    private function storePhoto(?UploadedFile $photo, string $directory): ?string
    {
        if (! $photo) {
            return null;
        }

        $extension = strtolower($photo->getClientOriginalExtension() ?: $photo->extension() ?: 'bin');
        $path = $directory . '/' . Str::uuid() . '.' . $extension;

        WimsStorage::storeUploadedFileAs($photo, $directory, basename($path));

        return $path;
    }
}
