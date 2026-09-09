<?php

namespace App\Modules\Wims\Services\Mitra;

use App\Models\Magang\LogbookMagang;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MitraLogbookReviewService
{
    public function review(LogbookMagang $logbook, int $reviewerUserId, array $validated): void
    {
        DB::transaction(function () use ($logbook, $reviewerUserId, $validated): void {
            $lockedLogbook = LogbookMagang::query()
                ->lockForUpdate()
                ->findOrFail($logbook->id);

            if ($lockedLogbook->status !== 'pending') {
                throw ValidationException::withMessages([
                    'logbook' => 'Logbook ini sudah diproses atau belum siap direview ulang.',
                ]);
            }

            $lockedLogbook->update([
                'status' => $validated['status'],
                'catatan_mitra' => $validated['catatan_mitra'] ?? null,
                'reviewed_by_mitra_user_id' => $reviewerUserId,
                'reviewed_by_mitra_at' => now(),
            ]);
        });
    }
}
