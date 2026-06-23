<?php

namespace App\Enums;

/**
 * Enum for career history types
 *
 * Defines whether a career record is employment, education, or unemployment
 */
enum CareerType: string
{
    /**
     * Employment record (working at a company)
     */
    case EMPLOYMENT = 'employment';

    /**
     * Education record (pursuing studies)
     */
    case EDUCATION = 'education';

    /**
     * Unemployment record (not working, seeking job)
     */
    case UNEMPLOYMENT = 'unemployment';

    /**
     * Get display name for the type
     */
    public function label(): string
    {
        return match ($this) {
            self::EMPLOYMENT => 'Pekerjaan',
            self::EDUCATION => 'Pendidikan',
            self::UNEMPLOYMENT => 'Tidak Bekerja',
        };
    }

    /**
     * Get icon name for the type
     */
    public function icon(): string
    {
        return match ($this) {
            self::EMPLOYMENT => 'briefcase',
            self::EDUCATION => 'graduation-cap',
            self::UNEMPLOYMENT => 'ban',
        };
    }

    /**
     * Get all available types for selection
     */
    public static function options(): array
    {
        return array_map(fn ($type) => [
            'value' => $type->value,
            'label' => $type->label(),
            'icon' => $type->icon(),
        ], self::cases());
    }
}
