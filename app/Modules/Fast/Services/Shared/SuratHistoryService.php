<?php
// app/Services/SuratHistoryService.php

namespace App\Modules\Fast\Services\Shared;

use App\Models\SuratHistory;
use Illuminate\Support\Facades\Auth;

class SuratHistoryService
{
    /**
     * Catat aksi ke surat_histories.
     */
    public static function record(
        int $suratId,
        string $action,
        string $label,
        array $extra = []
    ): SuratHistory {
        return SuratHistory::create([
            'surat_id'     => $suratId,
            'user_id'      => Auth::id(),
            'action'       => $action,
            'action_label' => $label,
            'keterangan'   => $extra['keterangan'] ?? null,
            'meta'         => $extra['meta'] ?? null,
            'ip_address'   => request()?->ip(),
        ]);
    }

    // â”€â”€ Shortcut methods â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public static function created(int $suratId, string $jenisSurat): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_CREATED,
            static::formatJenisSuratLabel($jenisSurat).' dibuat');
    }

    public static function submitted(int $suratId, string $jenisSurat): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_SUBMITTED,
            static::formatJenisSuratLabel($jenisSurat).' diajukan');
    }

    public static function validated(int $suratId, ?string $catatan = null): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_VALIDATED,
            'Surat divalidasi admin',
            ['keterangan' => $catatan]);
    }

    public static function approved(int $suratId, string $roleNama, ?string $catatan = null): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_APPROVED,
            "Surat disetujui {$roleNama}",
            ['keterangan' => $catatan]);
    }

    public static function rejected(int $suratId, string $alasan, string $label = 'Surat ditolak', array $meta = []): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_REJECTED,
            $label,
            [
                'keterangan' => $alasan,
                'meta' => $meta,
            ]);
    }

    public static function revisionRequested(int $suratId, int $revisiKe, string $roleNama, ?string $catatan = null): SuratHistory
    {
        return static::record(
            $suratId,
            SuratHistory::ACTION_REVISED,
            "Dikembalikan {$roleNama} untuk revisi",
            [
                'keterangan' => $catatan,
                'meta' => [
                    'revisi_ke' => $revisiKe,
                    'role' => strtolower($roleNama),
                ],
            ]
        );
    }

    public static function generated(int $suratId, string $nomorSurat): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_GENERATED,
            "Dokumen PDF digenerate - No: {$nomorSurat}");
    }

    public static function printed(int $suratId): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_PRINTED,
            'Surat dicetak');
    }

    public static function qrRevoked(int $suratId, string $alasan): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_QR_REVOKED,
            'QR Code dicabut',
            ['keterangan' => $alasan]);
    }

    public static function revised(int $suratId, int $revisiKe, ?string $catatan = null): SuratHistory
    {
        return static::record($suratId, SuratHistory::ACTION_REVISED,
            "Surat direvisi (revisi ke-{$revisiKe})",
            ['keterangan' => $catatan]);
    }

    protected static function formatJenisSuratLabel(string $jenisSurat): string
    {
        $label = trim($jenisSurat);

        if ($label === '') {
            return 'Surat';
        }

        if (preg_match('/^surat\b/i', $label)) {
            return $label;
        }

        return 'Surat '.$label;
    }
}
