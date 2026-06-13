<?php

namespace App\Modules\Wims\Services\Mitra;

use App\Models\KetidakhadiranMagang;
use App\Models\User;
use App\Services\KetidakhadiranService;

class MitraKetidakhadiranReviewService
{
    public function __construct(
        private readonly KetidakhadiranService $ketidakhadiranService,
    ) {
    }

    public function approve(KetidakhadiranMagang $ketidakhadiran, User $reviewer, ?string $note): void
    {
        $this->ketidakhadiranService->approve($ketidakhadiran, $reviewer, $note);
    }

    public function reject(KetidakhadiranMagang $ketidakhadiran, User $reviewer, ?string $note): void
    {
        $this->ketidakhadiranService->reject($ketidakhadiran, $reviewer, $note);
    }
}
