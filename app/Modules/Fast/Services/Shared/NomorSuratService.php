<?php
// app/Services/NomorSuratService.php

namespace App\Modules\Fast\Services\Shared;

use App\Models\NomorSuratSequence;
use App\Models\TemplateGlobalSetting;

class NomorSuratService
{
    /**
     * Generate nomor surat otomatis.
     *
     * Contoh hasil: CUTI-MHS/0042/IV/2026
     *
     * @param string $kodeSurat  Kode jenis surat, ambil dari jenis_surats.kode_surat
     * @return string
     */
    public static function generate(string $kodeSurat): string
    {
        return NomorSuratSequence::generate($kodeSurat);
    }

    /**
     * Generate dengan format kustom.
     * Placeholder: {kode}, {urutan}, {bulan_romawi}, {tahun}, {bulan}
     *
     * Contoh format: "B/{urutan}/{kode}/{tahun}"
     * Contoh hasil:  "B/0042/CUTI-MHS/2026"
     */
    public static function generateWithFormat(string $kodeSurat, string $format): string
    {
        return NomorSuratSequence::generate($kodeSurat, $format);
    }

    /**
     * Preview nomor berikutnya tanpa menyimpan ke DB.
     * Berguna untuk ditampilkan di form preview sebelum submit.
     */
    public static function preview(string $kodeSurat): string
    {
        $format = TemplateGlobalSetting::get('format_nomor', '{kode}/{urutan}/{bulan_romawi}/{tahun}');
        $tahun  = now()->year;
        $bulan  = now()->month;
        $romawi = ['', 'I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];

        // Ambil urutan saat ini (belum increment)
        $seq    = \App\Models\NomorSuratSequence::where([
            'kode_surat' => $kodeSurat,
            'tahun'      => $tahun,
            'bulan'      => $bulan,
        ])->first();

        $urutan = ($seq?->urutan ?? 0) + 1;

        return str_replace(
            ['{kode}', '{urutan}', '{bulan_romawi}', '{tahun}', '{bulan}'],
            [
                $kodeSurat,
                str_pad($urutan, 4, '0', STR_PAD_LEFT),
                $romawi[$bulan],
                $tahun,
                str_pad($bulan, 2, '0', STR_PAD_LEFT),
            ],
            $format
        );
    }
}
