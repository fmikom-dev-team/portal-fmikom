<?php

namespace App\Modules\Wims\Services\Mahasiswa\Dashboard;

use App\Models\Magang\AbsensiMagang;
use App\Models\Magang\KetidakhadiranMagang;
use App\Models\Magang\LogbookMagang;
use App\Models\Magang\PendaftaranMagang;
use App\Modules\Wims\Services\Shared\Attendance\AttendanceSyncService;
use App\Modules\Wims\Services\Shared\Portal\WimsModuleRoleService;
use App\Models\User;
use Illuminate\Support\Carbon;

class StudentDashboardPageService
{
    public function __construct(
        private readonly AttendanceSyncService $attendanceSyncService,
        private readonly WimsModuleRoleService $wimsModuleRoleService,
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

        $activeRegistration = $registrations->firstWhere('status', 'aktif');
        $latestRegistration = $registrations->first();
        $progressSource = $this->resolveProgressSource($activeRegistration, $latestRegistration);
        $historySource = $this->resolveHistorySource($activeRegistration, $latestRegistration);
        $dashboardState = $this->resolveDashboardState($activeRegistration, $latestRegistration);

        $attendanceToday = AbsensiMagang::query()
            ->where('pendaftaran_id', $activeRegistration?->id)
            ->whereDate('tanggal', now()->toDateString())
            ->first();
        $canDoDailyActivity = $activeRegistration?->allowsDailyActivity() ?? false;

        $approvedAbsenceToday = $activeRegistration
            ? KetidakhadiranMagang::query()
                ->where('pendaftaran_id', $activeRegistration->id)
                ->where('status', 'approved')
                ->whereDate('tanggal_mulai', '<=', now()->toDateString())
                ->whereDate('tanggal_selesai', '>=', now()->toDateString())
                ->latest('id')
                ->first()
            : null;

        $history = AbsensiMagang::query()
            ->where('pendaftaran_id', $historySource?->id)
            ->latest('tanggal')
            ->latest('timestamp_masuk')
            ->get()
            ->map(fn (AbsensiMagang $attendance) => [
                'id' => $attendance->id,
                'date' => optional($attendance->tanggal)->translatedFormat('d M Y'),
                'time' => $attendance->timestamp_masuk?->format('H:i'),
                'status' => match ($attendance->status) {
                    'terlambat' => 'Terlambat',
                    'izin' => 'Izin',
                    'sakit' => 'Sakit',
                    'alfa' => 'Alfa',
                    default => 'Tepat Waktu',
                },
                'label' => match ($attendance->status) {
                    'terlambat' => 'Terlambat',
                    'izin' => 'Izin',
                    'sakit' => 'Sakit',
                    'alfa' => 'Alfa',
                    default => 'Tepat Waktu',
                },
            ])
            ->values();

        [$progressPercentage, $totalDays, $completedDays, $remainingDays] = $this->buildProgress($progressSource);

        $latestLogbook = LogbookMagang::query()
            ->where('pendaftaran_id', $historySource?->id)
            ->latest('tanggal')
            ->latest('id')
            ->first();

        return [
            'user' => [
                'name' => $user->name,
                'avatar' => $user->photoUrl(),
            ],
            'attendance' => [
                'status' => $attendanceToday?->timestamp_keluar
                    ? 'checked_out'
                    : ($attendanceToday?->timestamp_masuk ? 'checked_in' : ($approvedAbsenceToday ? 'excused_absence' : 'not_checked_in')),
                'current_time' => now()->format('H:i') . ' WIB',
                'location_status' => ! $activeRegistration
                    ? 'Pendaftaran aktif belum tersedia'
                    : (! $canDoDailyActivity
                        ? 'Menunggu tanggal mulai periode PKL'
                        : ($attendanceToday?->lokasi_valid === null
                            ? 'Lokasi belum tervalidasi'
                            : ($attendanceToday->lokasi_valid ? 'Lokasi terdeteksi (akan divalidasi saat absen)' : 'Lokasi di luar area'))),
                'check_in_time' => $attendanceToday?->timestamp_masuk?->format('H:i'),
                'check_out_time' => $attendanceToday?->timestamp_keluar?->format('H:i'),
                'is_late' => $attendanceToday?->status === 'terlambat',
                'can_check_in' => $canDoDailyActivity && ! $attendanceToday?->timestamp_masuk,
                'can_check_out' => $canDoDailyActivity && (bool) $attendanceToday?->timestamp_masuk && ! $attendanceToday?->timestamp_keluar,
            ],
            'internship' => [
                'progress_percentage' => $progressPercentage,
                'completed_days' => $completedDays,
                'total_days' => $totalDays,
                'remaining_days' => $remainingDays,
            ],
            'registration' => [
                'status' => $latestRegistration?->status,
                'dashboard_state' => $dashboardState,
                'company' => [
                    'proposal' => [
                        'name' => $latestRegistration?->perusahaan_diminati_nama,
                    ],
                    'final' => [
                        'id' => $latestRegistration?->perusahaan?->id,
                        'name' => $latestRegistration?->perusahaan?->nama,
                    ],
                ],
                'lecturer' => [
                    'id' => $latestRegistration?->dosenPembimbing?->id,
                    'name' => $latestRegistration?->dosenPembimbing?->name,
                    'role_context' => $latestRegistration?->dosenPembimbing
                        ? $this->wimsModuleRoleService->resolveContextRoleData($latestRegistration->dosenPembimbing, 'dosen')
                        : null,
                ],
                'mentor' => [
                    'id' => $latestRegistration?->perusahaan?->user?->id,
                    'name' => $latestRegistration?->perusahaan?->user?->name,
                    'role_context' => $latestRegistration?->perusahaan?->user
                        ? $this->wimsModuleRoleService->resolveContextRoleData($latestRegistration->perusahaan->user, 'mitra')
                        : null,
                ],
                'submitted_at' => $latestRegistration?->created_at?->translatedFormat('d M Y H:i'),
                'period_label' => $latestRegistration?->tanggal_mulai && $latestRegistration?->tanggal_selesai
                    ? Carbon::parse($latestRegistration->tanggal_mulai)->translatedFormat('d M Y')
                        . ' - '
                        . Carbon::parse($latestRegistration->tanggal_selesai)->translatedFormat('d M Y')
                    : null,
            ],
            'latest_logbook' => [
                'date' => $latestLogbook?->tanggal?->translatedFormat('d M Y'),
                'status' => $latestLogbook?->status,
                'activity' => $latestLogbook?->aktivitas_harian,
                'reviewer_note' => $latestLogbook?->catatan_mitra ?? $latestLogbook?->catatan_dosen,
            ],
            'history' => $history,
        ];
    }

    private function resolveDashboardState(?PendaftaranMagang $activeRegistration, ?PendaftaranMagang $latestRegistration): string
    {
        if ($latestRegistration?->isPostInternshipPhase()) {
            return 'completed';
        }

        if ($activeRegistration) {
            return 'active';
        }

        if ($latestRegistration?->status) {
            return 'waiting';
        }

        return 'not_registered';
    }

    private function resolveProgressSource(?PendaftaranMagang $activeRegistration, ?PendaftaranMagang $latestRegistration): ?PendaftaranMagang
    {
        if ($activeRegistration) {
            return $activeRegistration;
        }

        if ($latestRegistration?->status === 'selesai') {
            return $latestRegistration;
        }

        if ($latestRegistration?->isPostInternshipPhase()) {
            return $latestRegistration;
        }

        return null;
    }

    private function resolveHistorySource(?PendaftaranMagang $activeRegistration, ?PendaftaranMagang $latestRegistration): ?PendaftaranMagang
    {
        if ($activeRegistration) {
            return $activeRegistration;
        }

        if ($latestRegistration?->isPostInternshipPhase()) {
            return $latestRegistration;
        }

        return null;
    }

    private function buildProgress(?PendaftaranMagang $progressSource): array
    {
        $totalDays = null;
        $completedDays = null;
        $remainingDays = null;
        $progressPercentage = 0;

        if ($progressSource?->tanggal_mulai && $progressSource?->tanggal_selesai) {
            $startDate = Carbon::parse($progressSource->tanggal_mulai)->startOfDay();
            $endDate = Carbon::parse($progressSource->tanggal_selesai)->startOfDay();
            $effectiveEndDate = $progressSource->status === 'selesai'
                ? $endDate
                : (now()->startOfDay()->lessThan($endDate)
                    ? now()->startOfDay()
                    : $endDate);

            $totalDays = $startDate->diffInDays($endDate) + 1;
            $completedDays = min(
                $totalDays,
                max(0, $startDate->diffInDays($effectiveEndDate) + 1),
            );
            $remainingDays = max(0, $totalDays - $completedDays);
            $progressPercentage = $totalDays > 0
                ? (int) round(($completedDays / $totalDays) * 100)
                : 0;
        }

        return [$progressPercentage, $totalDays, $completedDays, $remainingDays];
    }
}
