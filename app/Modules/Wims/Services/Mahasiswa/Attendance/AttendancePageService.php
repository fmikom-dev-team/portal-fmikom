<?php

namespace App\Modules\Wims\Services\Mahasiswa\Attendance;

use App\Models\Magang\AbsensiMagang;
use App\Models\Magang\KetidakhadiranMagang;
use App\Models\Magang\PendaftaranMagang;
use App\Models\User;
use App\Services\AttendanceSyncService;
use App\Services\KetidakhadiranService;

class AttendancePageService
{
    public function __construct(
        private readonly AttendanceSyncService $attendanceSyncService,
        private readonly KetidakhadiranService $ketidakhadiranService,
        private readonly AttendanceAvailabilityService $attendanceAvailabilityService,
    ) {
    }

    public function build(User $user): array
    {
        $registrations = PendaftaranMagang::with('perusahaan')
            ->forMahasiswa($user->id)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->get();
        $this->attendanceSyncService->syncForRegistrations($registrations);

        $attendanceHistoryCount = AbsensiMagang::query()
            ->whereHas('pendaftaran', fn ($query) => $query->where('mahasiswa_id', $user->id))
            ->count();

        $pendaftaran = $registrations->firstWhere('status', 'aktif');

        $absensiHariIni = AbsensiMagang::query()
            ->where('pendaftaran_id', $pendaftaran?->id)
            ->whereDate('tanggal', now()->toDateString())
            ->first();

        $approvedAbsenceToday = $pendaftaran
            ? $this->ketidakhadiranService->getApprovedAbsenceOnDate($pendaftaran, now())
            : null;

        [$canAttendToday, $workdayMessage] = $pendaftaran
            ? $this->attendanceAvailabilityService->resolveAvailability($pendaftaran)
            : [false, 'Pendaftaran PKL/magang belum aktif. Tunggu keputusan dan penempatan final dari kampus.'];

        $absenceRequests = KetidakhadiranMagang::query()
            ->where('mahasiswa_id', $user->id)
            ->when($pendaftaran, fn ($query) => $query->where('pendaftaran_id', $pendaftaran->id))
            ->latest('tanggal_mulai')
            ->latest('id')
            ->limit(8)
            ->get()
            ->map(fn (KetidakhadiranMagang $item) => [
                'id' => $item->id,
                'tanggal_mulai' => $item->tanggal_mulai?->toDateString(),
                'tanggal_selesai' => $item->tanggal_selesai?->toDateString(),
                'tanggal_label' => $item->tanggal_mulai && $item->tanggal_selesai
                    ? ($item->tanggal_mulai->isSameDay($item->tanggal_selesai)
                        ? $item->tanggal_mulai->translatedFormat('d M Y')
                        : $item->tanggal_mulai->translatedFormat('d M Y') . ' - ' . $item->tanggal_selesai->translatedFormat('d M Y'))
                    : '-',
                'jenis' => $item->jenis,
                'alasan' => $item->alasan,
                'status' => $item->status,
                'catatan_mitra' => $item->catatan_mitra,
                'can_cancel' => $item->status === 'pending',
                'bukti_url' => $item->bukti_path ? '/storage/' . $item->bukti_path : null,
            ])
            ->values();

        return [
            'pendaftaran_id' => $pendaftaran?->id,
            'company_name' => $pendaftaran?->perusahaan?->nama,
            'radius' => $pendaftaran?->perusahaan?->radius_valid_meter,
            'office_latitude' => $pendaftaran?->perusahaan?->latitude,
            'office_longitude' => $pendaftaran?->perusahaan?->longitude,
            'status' => $absensiHariIni?->timestamp_keluar
                ? 'checked_out'
                : ($absensiHariIni?->timestamp_masuk
                    ? 'checked_in'
                    : ($approvedAbsenceToday ? 'excused_absence' : 'not_checked_in')),
            'check_in_time' => $absensiHariIni?->timestamp_masuk,
            'check_out_time' => $absensiHariIni?->timestamp_keluar,
            'check_in_photo_url' => $absensiHariIni?->checkInPhotoUrl(),
            'check_out_photo_url' => $absensiHariIni?->checkOutPhotoUrl(),
            'can_check_in' => $pendaftaran !== null && $canAttendToday && ! $absensiHariIni?->timestamp_masuk,
            'can_check_out' => $pendaftaran !== null && $canAttendToday && (bool) $absensiHariIni?->timestamp_masuk && ! $absensiHariIni?->timestamp_keluar,
            'can_attend_today' => $canAttendToday,
            'workday_message' => $workdayMessage,
            'is_late' => $absensiHariIni?->status === 'terlambat',
            'today_absence' => $approvedAbsenceToday ? [
                'jenis' => $approvedAbsenceToday->jenis,
                'status' => $approvedAbsenceToday->status,
                'alasan' => $approvedAbsenceToday->alasan,
                'tanggal_label' => $approvedAbsenceToday->tanggal_mulai && $approvedAbsenceToday->tanggal_selesai
                    ? ($approvedAbsenceToday->tanggal_mulai->isSameDay($approvedAbsenceToday->tanggal_selesai)
                        ? $approvedAbsenceToday->tanggal_mulai->translatedFormat('d M Y')
                        : $approvedAbsenceToday->tanggal_mulai->translatedFormat('d M Y') . ' - ' . $approvedAbsenceToday->tanggal_selesai->translatedFormat('d M Y'))
                    : null,
            ] : null,
            'current_time' => now()->toIso8601String(),
            'location_status' => is_null($absensiHariIni?->lokasi_valid)
                ? null
                : ($absensiHariIni->lokasi_valid ? 'valid' : 'invalid'),
            'absensi_hari_ini' => [
                'masuk' => $absensiHariIni?->timestamp_masuk,
                'keluar' => $absensiHariIni?->timestamp_keluar,
            ],
            'history_count' => $attendanceHistoryCount,
            'history_download_url' => $attendanceHistoryCount > 0 ? route('wims.absensi.download') : null,
            'current_period_history_download_url' => $pendaftaran?->id
                ? route('wims.absensi.download', ['scope' => 'current'])
                : null,
            'absence_requests' => $absenceRequests->all(),
        ];
    }
}
