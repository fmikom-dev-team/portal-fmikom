<?php

namespace App\Modules\Wims\Services\Mitra;

use App\Models\Magang\LogbookMagang;

class MitraLogbookReviewService
{
    public function review(LogbookMagang $logbook, int $reviewerUserId, array $validated): void
    {
        $logbook->update([
            'status' => $validated['status'],
            'catatan_mitra' => $validated['catatan_mitra'] ?? null,
            'reviewed_by_mitra_user_id' => $reviewerUserId,
            'reviewed_by_mitra_at' => now(),
        ]);
    }
}
