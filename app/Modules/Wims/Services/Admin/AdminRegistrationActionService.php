<?php

namespace App\Modules\Wims\Services\Admin;

use App\Models\Magang\PendaftaranMagang;

class AdminRegistrationActionService
{
    public function updateStatus(PendaftaranMagang $pendaftaran, array $validated): void
    {
        $pendaftaran->update([
            'status' => $validated['status'],
            'catatan_revisi_admin' => $validated['status'] === 'revisi'
                ? trim((string) ($validated['catatan_revisi_admin'] ?? ''))
                : null,
        ]);
    }
}
