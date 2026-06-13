<?php

namespace App\Modules\Wims\Support;

use App\Models\Magang\AbsensiMagang;
use App\Models\KetidakhadiranMagang;
use App\Models\Magang\LogbookMagang;
use App\Models\Magang\PendaftaranMagang;
use App\Models\User;
use App\Services\AttendanceSyncService;

class WimsStudentAlerts
{
    public function __construct(
        private readonly AttendanceSyncService $attendanceSyncService,
    ) {
    }

    /**
     * @return array<int, array{id: string, message: string}>
     */
    public function forUser(User $user): array
    {
        $registrations = PendaftaranMagang::with('perusahaan')
            ->forMahasiswa($user->id)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->get();

        $this->attendanceSyncService->syncForRegistrations($registrations);

        $activeRegistration = $registrations->firstWhere('status', 'aktif');

        if (! $activeRegistration) {
            return [];
        }

        if (! $activeRegistration->allowsDailyActivity()) {
            return [];
        }

        $attendanceToday = AbsensiMagang::query()
            ->where('pendaftaran_id', $activeRegistration->id)
            ->whereDate('tanggal', now()->toDateString())
            ->first();

        $approvedAbsenceToday = KetidakhadiranMagang::query()
            ->where('pendaftaran_id', $activeRegistration->id)
            ->where('status', 'approved')
            ->whereDate('tanggal_mulai', '<=', now()->toDateString())
            ->whereDate('tanggal_selesai', '>=', now()->toDateString())
            ->latest('id')
            ->first();

        $hasTodayLogbook = LogbookMagang::query()
            ->where('pendaftaran_id', $activeRegistration->id)
            ->whereDate('tanggal', now()->toDateString())
            ->exists();

        $alerts = [];

        if (! $attendanceToday?->timestamp_masuk && ! $approvedAbsenceToday) {
            $alerts[] = [
                'id' => 'daily-attendance',
                'message' => 'Anda belum melakukan presensi hari ini.',
            ];
        }

        if (! $hasTodayLogbook && ! $approvedAbsenceToday) {
            $alerts[] = [
                'id' => 'daily-logbook',
                'message' => 'Anda belum mengisi logbook hari ini.',
            ];
        }

        return $alerts;
    }
}
