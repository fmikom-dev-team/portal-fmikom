<?php

namespace App\Enums;

/**
 * Enum for career employment status
 *
 * Defines the possible employment status values for alumni career records
 * including employment, entrepreneurship, further study, and job seeking.
 */
enum CareerStatus: string
{
    /**
     * Currently employed at a company
     */
    case BEKERJA = 'bekerja';

    /**
     * Self-employed or running a business
     */
    case WIRAUSAHA = 'wirausaha';

    /**
     * Pursuing further education/studies
     */
    case LANJUT_STUDI = 'lanjut_studi';

    /**
     * Currently seeking employment
     */
    case MENCARI_KERJA = 'mencari_kerja';

    /**
     * Get display name for the status
     */
    public function label(): string
    {
        return match ($this) {
            self::BEKERJA => 'Bekerja',
            self::WIRAUSAHA => 'Wirausaha',
            self::LANJUT_STUDI => 'Lanjut Studi',
            self::MENCARI_KERJA => 'Mencari Kerja',
        };
    }

    /**
     * Get description for the status
     */
    public function description(): string
    {
        return match ($this) {
            self::BEKERJA => 'Bekerja sebagai karyawan di sebuah perusahaan',
            self::WIRAUSAHA => 'Menjalankan usaha atau bisnis sendiri',
            self::LANJUT_STUDI => 'Melanjutkan pendidikan ke jenjang lebih tinggi',
            self::MENCARI_KERJA => 'Sedang mencari pekerjaan',
        };
    }

    /**
     * Get the badge color for the status
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::BEKERJA => 'success',
            self::WIRAUSAHA => 'info',
            self::LANJUT_STUDI => 'primary',
            self::MENCARI_KERJA => 'warning',
        };
    }

    /**
     * Get all available statuses for selection
     */
    public static function options(): array
    {
        return array_map(fn ($status) => [
            'value' => $status->value,
            'label' => $status->label(),
            'description' => $status->description(),
            'color' => $status->badgeColor(),
        ], self::cases());
    }
}
