<?php

namespace App\Modules\Wims\Services\Mahasiswa\Attendance;

use App\Models\Magang\HariLibur;
use App\Models\Magang\PendaftaranMagang;
use App\Services\KetidakhadiranService;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class AttendanceAvailabilityService
{
    public function __construct(
        private readonly KetidakhadiranService $ketidakhadiranService,
    ) {
    }

    public function resolveAvailability(PendaftaranMagang $pendaftaran): array
    {
        // Presensi hanya dibuka setelah admin mengaktifkan PKL agar tidak ada absensi
        // yang dilakukan sebelum penempatan final benar-benar disahkan.
        if (! $pendaftaran->isActivatedByAdmin()) {
            return [false, 'Presensi belum dibuka. Tunggu sampai admin menetapkan penempatan final dan mengaktifkan PKL/magang Anda.'];
        }

        // Sistem membatasi presensi hanya pada rentang tanggal magang yang telah ditetapkan.
        if (! $pendaftaran->isWithinActivePeriod()) {
            $periodeMulai = $pendaftaran->tanggal_mulai?->translatedFormat('d M Y') ?? '-';
            $periodeSelesai = $pendaftaran->tanggal_selesai?->translatedFormat('d M Y') ?? '-';

            return [false, "Presensi hanya dapat dilakukan sesuai periode PKL yang diajukan, yaitu {$periodeMulai} s/d {$periodeSelesai}."];
        }

        $approvedAbsence = $this->ketidakhadiranService->getApprovedAbsenceOnDate($pendaftaran, now());

        if ($approvedAbsence) {
            $absenceLabel = match ($approvedAbsence->jenis) {
                'sakit' => 'sakit',
                'izin' => 'izin',
                default => 'ketidakhadiran resmi',
            };

            return [false, "Hari ini tercatat sebagai {$absenceLabel} yang telah disetujui pembimbing mitra."];
        }

        return $this->resolveDayStatus($pendaftaran->perusahaan);
    }

    public function resolveStatus(CarbonInterface $checkedAt, ?string $jamMasuk, mixed $toleransiMenit): string
    {
        if (blank($jamMasuk)) {
            return 'hadir';
        }

        // Status hadir atau terlambat ditentukan dari jam masuk perusahaan
        // yang ditambah toleransi keterlambatan per mitra.
        $tolerance = (int) ($toleransiMenit ?? 0);
        $latestOnTime = Carbon::parse($checkedAt->toDateString() . ' ' . $jamMasuk)->addMinutes($tolerance);

        return $checkedAt->greaterThan($latestOnTime) ? 'terlambat' : 'hadir';
    }

    private function resolveDayStatus(mixed $perusahaan): array
    {
        if (! $perusahaan) {
            return [true, null];
        }

        $today = now()->startOfDay();
        $holidayDates = HariLibur::query()
            ->where('is_active', true)
            ->whereDate('tanggal', $today->toDateString())
            ->pluck('tanggal')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->flip();

        if ($perusahaan->worksOnDate($today, $holidayDates)) {
            return [true, null];
        }

        // Hari kerja efektif mempertimbangkan hari libur nasional dan konfigurasi hari kerja perusahaan.
        if ($holidayDates->has($today->toDateString())) {
            return [false, 'Hari ini termasuk libur nasional/tanggal merah, sehingga presensi tidak dibuka.'];
        }

        if ($today->isSunday()) {
            return [false, 'Hari ini hari Minggu, sehingga presensi tidak dibuka.'];
        }

        $dayLabel = match (strtolower($today->englishDayOfWeek)) {
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            default => 'hari ini',
        };

        return [false, "Hari {$dayLabel} tidak termasuk hari kerja perusahaan Anda, sehingga presensi tidak dibuka."];
    }
}
