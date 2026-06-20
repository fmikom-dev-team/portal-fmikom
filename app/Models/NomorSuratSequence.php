<?php
// app/Models/NomorSuratSequence.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NomorSuratSequence extends Model
{
    protected $fillable = ['kode_surat', 'tahun', 'bulan', 'urutan'];

    /**
     * Ambil nomor urut berikutnya untuk kode + bulan + tahun tertentu.
     * Menggunakan DB lock agar aman dari race condition (concurrent request).
     *
     * @return int nomor urut (sudah di-increment)
     */
    public static function nextUrutan(string $kodeSurat, ?int $tahun = null, ?int $bulan = null): int
    {
        $tahun = $tahun ?? now()->year;
        $bulan = $bulan ?? now()->month;

        return DB::transaction(function () use ($kodeSurat, $tahun, $bulan) {
            $seq = static::lockForUpdate()->firstOrCreate(
                ['kode_surat' => $kodeSurat, 'tahun' => $tahun, 'bulan' => $bulan],
                ['urutan' => 0]
            );

            $seq->increment('urutan');
            $seq->refresh();

            return $seq->urutan;
        });
    }

    /**
     * Format nomor surat lengkap.
     * Default: KODE/0001/IV/2026
     */
    public static function generate(string $kodeSurat, ?string $format = null): string
    {
        $format  = $format ?? TemplateGlobalSetting::get('format_nomor', '{kode}/{urutan}/{bulan_romawi}/{tahun}');
        $tahun   = now()->year;
        $bulan   = now()->month;
        $urutan  = static::nextUrutan($kodeSurat, $tahun, $bulan);

        $romawi  = ['', 'I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];

        return str_replace(
            ['{kode}', '{urutan}', '{bulan_romawi}', '{tahun}', '{bulan}'],
            [$kodeSurat, str_pad($urutan, 4, '0', STR_PAD_LEFT), $romawi[$bulan], $tahun, str_pad($bulan, 2, '0', STR_PAD_LEFT)],
            $format
        );
    }
}
