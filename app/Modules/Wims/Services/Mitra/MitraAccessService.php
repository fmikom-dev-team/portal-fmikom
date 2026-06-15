<?php

namespace App\Modules\Wims\Services\Mitra;

use App\Models\Magang\KetidakhadiranMagang;
use App\Models\Magang\LogbookMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\User;

class MitraAccessService
{
    public function resolveCompany(?User $user): ?PerusahaanMitra
    {
        if (! $user) {
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

        return (int) $ketidakhadiran->perusahaan_id === (int) $company->id;
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
