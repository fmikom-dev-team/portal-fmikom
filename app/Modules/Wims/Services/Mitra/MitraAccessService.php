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
        $registration = $ketidakhadiran->pendaftaran;

        // Gunakan perusahaan pada pendaftaran sebagai sumber otorisasi utama.
        // perusahaan_id pada ketidakhadiran hanya denormalisasi untuk kebutuhan
        // query dan harus tetap konsisten dengan pendaftaran terkait.
        return $registration !== null
            && (int) $registration->perusahaan_id === (int) $company->id
            && (int) $ketidakhadiran->getAttribute('perusahaan_id') === (int) $company->id
            && (int) $ketidakhadiran->getAttribute('mahasiswa_id') === (int) $registration->mahasiswa_id;
    }

    public function authorizeLogbookReview(User $user, LogbookMagang $logbook): void
    {
        $company = $this->resolveCompany($user);

        abort_unless($company !== null, 403);

        $logbook->loadMissing('pendaftaran');
        $registration = $logbook->pendaftaran;

        abort_unless(
            $registration !== null && (int) $registration->perusahaan_id === (int) $company->id,
            403,
        );
    }
}
