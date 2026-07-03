<?php

namespace App\Modules\Wims\Services\Admin;

use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use App\Modules\Wims\Services\Shared\Portal\WimsModuleRoleService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class AdminCompanyPageService
{
    public function __construct(
        private readonly WimsModuleRoleService $wimsModuleRoleService,
    ) {}

    public function build(string $search): array
    {
        $query = PerusahaanMitra::query()
            ->with('user')
            ->orderByDesc('is_active')
            ->orderBy('nama');

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('nama', 'like', "%{$search}%")
                    ->orWhere('kota', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('bidang_industri', 'like', "%{$search}%");
            });
        }

        $companies = $query
            ->get();

        $this->wimsModuleRoleService->preloadContextRoles(
            $companies->pluck('user')->filter()->all(),
        );

        $linkedCompaniesByUserId = $companies
            ->filter(fn (PerusahaanMitra $company) => filled($company->user_id))
            ->mapWithKeys(fn (PerusahaanMitra $company) => [
                (int) $company->user_id => [
                    'company_id' => $company->id,
                    'company_name' => $company->nama,
                ],
            ]);

        $portalMitraUsers = $this->wimsModuleRoleService->usersForRole('mitra')
            ->with(['moduleRoles.module'])
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'no_telepon', 'is_active']);

        /** @var Collection<int, User> $portalMitraUsers */
        $portalMitraUsers = $portalMitraUsers;

        $companies = $companies
            ->map(fn (PerusahaanMitra $company) => [
                'id' => $company->id,
                'nama' => $company->nama,
                'alamat' => $company->alamat,
                'kota' => $company->kota,
                'latitude' => $company->latitude,
                'longitude' => $company->longitude,
                'radius_valid_meter' => $company->radius_valid_meter,
                'jam_masuk' => $this->formatTime($company->jam_masuk),
                'jam_pulang' => $this->formatTime($company->jam_pulang),
                'toleransi_terlambat_menit' => $company->toleransi_terlambat_menit,
                'hari_kerja' => $company->getWorkingDays(),
                'bidang_industri' => $company->bidang_industri,
                'kontak_person' => $company->kontak_person,
                'telepon' => $company->telepon,
                'email' => $company->email,
                'is_active' => $company->is_active,
                'account' => $company->user ? [
                    'id' => $company->user->id,
                    'user_id' => $company->user->id,
                    'name' => $company->user->name,
                    'email' => $company->user->email,
                    'phone' => $company->user->no_telepon,
                    'jabatan' => $company->mitra_jabatan,
                    'is_active' => (bool) $company->user->is_active,
                    'role_context' => $this->wimsModuleRoleService->resolveContextRoleData($company->user, 'mitra'),
                ] : null,
            ])
            ->values();

        $portalUsers = $portalMitraUsers
            ->map(function (User $user) use ($linkedCompaniesByUserId): array {
                $linkedCompany = $linkedCompaniesByUserId->get((int) $user->id);

                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->no_telepon,
                    'is_active' => (bool) $user->is_active,
                    'label' => trim(sprintf('%s - %s', $user->name ?: 'Tanpa nama', $user->email)),
                    'linked_company_id' => $linkedCompany['company_id'] ?? null,
                    'linked_company_name' => $linkedCompany['company_name'] ?? null,
                ];
            })
            ->values();

        return [
            'filters' => [
                'search' => $search,
            ],
            'summary' => [
                'total' => PerusahaanMitra::count(),
                'active' => PerusahaanMitra::where('is_active', true)->count(),
                'configured_location' => PerusahaanMitra::query()
                    ->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->count(),
                'configured_schedule' => PerusahaanMitra::query()
                    ->whereNotNull('jam_masuk')
                    ->whereNotNull('jam_pulang')
                    ->count(),
                'mitra_accounts' => PerusahaanMitra::query()->whereNotNull('user_id')->count(),
            ],
            'workingDayOptions' => collect(PerusahaanMitra::workingDayOptions())
                ->map(fn (string $value) => [
                    'value' => $value,
                    'label' => $this->formatWorkingDayLabel($value),
                ])
                ->all(),
            'companies' => $companies->all(),
            'portalUsers' => $portalUsers->all(),
        ];
    }

    private function formatTime(mixed $time): ?string
    {
        if (blank($time)) {
            return null;
        }

        return Carbon::parse((string) $time)->format('H:i');
    }

    private function formatWorkingDayLabel(string $value): string
    {
        return match ($value) {
            'monday' => 'Senin',
            'tuesday' => 'Selasa',
            'wednesday' => 'Rabu',
            'thursday' => 'Kamis',
            'friday' => 'Jumat',
            'saturday' => 'Sabtu',
            default => ucfirst($value),
        };
    }
}
