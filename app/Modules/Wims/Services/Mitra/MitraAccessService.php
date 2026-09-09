<?php

namespace App\Modules\Wims\Services\Mitra;

use App\Models\Magang\KetidakhadiranMagang;
use App\Models\Magang\LogbookMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use App\Modules\Wims\Services\Shared\Portal\WimsModuleRoleService;

class MitraAccessService
{
    public function __construct(
        private readonly WimsModuleRoleService $wimsModuleRoleService,
    ) {}

    public function resolveCompany(?User $user): ?PerusahaanMitra
    {
        if (! $user || ! $this->wimsModuleRoleService->hasActiveRole($user->id, 'mitra')) {
            return null;
        }

        return PerusahaanMitra::query()
            ->where('user_id', $user->id)
            ->first();
    }

    public function canReviewAbsence(User $user, KetidakhadiranMagang $ketidakhadiran): bool
    {
        $company = $this->resolveCompany($user);

        if (! $company) {
            return false;
        }

        $ketidakhadiran->loadMissing('pendaftaran');

        // Gunakan perusahaan pada pendaftaran sebagai sumber otorisasi utama.
        // perusahaan_id pada ketidakhadiran hanya denormalisasi untuk kebutuhan
        // query dan harus tetap konsisten dengan pendaftaran terkait.
        return (int) $ketidakhadiran->pendaftaran?->perusahaan_id === (int) $company->id
            && (int) $ketidakhadiran->perusahaan_id === (int) $company->id
            && (int) $ketidakhadiran->mahasiswa_id === (int) $ketidakhadiran->pendaftaran?->mahasiswa_id;
    }

    public function authorizeLogbookReview(User $user, LogbookMagang $logbook): void
    {
        $company = $this->resolveCompany($user);

        abort_unless($company !== null, 403);

        $logbook->loadMissing('pendaftaran');

        abort_unless(
            (int) $logbook->pendaftaran?->perusahaan_id === (int) $company->id,
            403,
        );
    }
}
