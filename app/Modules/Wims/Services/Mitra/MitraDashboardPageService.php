<?php

namespace App\Modules\Wims\Services\Mitra;

use App\Models\Magang\KetidakhadiranMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use App\Modules\Wims\Services\Shared\Monitoring\MonitoringAlertService;
use App\Modules\Wims\Services\Shared\Portal\WimsModuleRoleService;

class MitraDashboardPageService
{
    public function __construct(
        private readonly MitraAccessService $mitraAccessService,
        private readonly MitraMonitoringOverviewService $monitoringOverviewService,
        private readonly MonitoringAlertService $monitoringAlertService,
        private readonly WimsModuleRoleService $wimsModuleRoleService,
    ) {}

    public function build(User $user): array
    {
        $company = $this->mitraAccessService->resolveCompany($user);
        $today = now()->toDateString();

        if (! $company) {
            return [
                'mentor' => $this->buildMentorPayload($user, null),
                'summary' => $this->emptySummary(),
                'students' => [],
                'attendanceBoard' => [],
                'reviewBoard' => [],
                'pendingAbsenceRequests' => [],
                'warnings' => [],
            ];
        }

        $overview = $this->monitoringOverviewService->buildOverview($user, $company, $today);
        $warnings = $this->monitoringAlertService->getWarningsForCompany($company);

        $pendingAbsenceBaseQuery = KetidakhadiranMagang::query()
            ->where('status', 'pending')
            ->where('perusahaan_id', $company->id);

        $pendingAbsenceRequests = (clone $pendingAbsenceBaseQuery)
            ->with(['mahasiswa', 'perusahaan'])
            ->latest('submitted_at')
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (KetidakhadiranMagang $item) => [
                'id' => $item->id,
                'name' => $item->mahasiswa?->name,
                'nim' => $item->mahasiswa?->nim_nip ?: $item->mahasiswa?->nomor_induk,
                'company' => [
                    'id' => $item->perusahaan?->id,
                    'name' => $item->perusahaan?->nama,
                ],
                'jenis' => $item->jenis,
                'alasan' => str($item->alasan)->squish()->limit(120)->toString(),
                'tanggal_label' => $item->tanggal_mulai && $item->tanggal_selesai
                    ? ($item->tanggal_mulai->isSameDay($item->tanggal_selesai)
                        ? $item->tanggal_mulai->translatedFormat('d M Y')
                        : $item->tanggal_mulai->translatedFormat('d M Y').' - '.$item->tanggal_selesai->translatedFormat('d M Y'))
                    : '-',
            ])
            ->values()
            ->all();

        return [
            'mentor' => $this->buildMentorPayload($user, $company),
            'summary' => [
                ...$overview['summary'],
                'pending_absence_requests' => (clone $pendingAbsenceBaseQuery)->count(),
                'active_warnings' => $warnings->count(),
            ],
            'students' => $overview['students']->all(),
            'attendanceBoard' => $overview['attendanceBoard'],
            'reviewBoard' => $overview['reviewBoard'],
            'pendingAbsenceRequests' => $pendingAbsenceRequests,
            'warnings' => $warnings->all(),
        ];
    }

    private function buildMentorPayload(User $user, ?PerusahaanMitra $company): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->no_telepon,
            'jabatan' => $company?->mitra_jabatan,
            'company_city' => $company?->kota,
            'role_context' => $this->wimsModuleRoleService->resolveContextRoleData($user, 'mitra'),
            'company' => [
                'id' => $company?->id,
                'name' => $company?->nama,
                'city' => $company?->kota,
            ],
        ];
    }

    private function emptySummary(): array
    {
        return [
            'total_students' => 0,
            'upcoming_students' => 0,
            'active_students' => 0,
            'completed_students' => 0,
            'not_present_today' => 0,
            'needs_review' => 0,
            'not_evaluated' => 0,
            'active_warnings' => 0,
            'pending_absence_requests' => 0,
        ];
    }
}
