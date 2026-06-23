<?php

namespace App\Enums;

/**
 * Enum for question types in questionnaires
 * 
 * Defines all possible question types that can be used in a questionnaire
 * including single-choice, multiple-choice, text inputs, and matrices.
 */
enum QuestionType: string
{
    /**
     * Radio button selection (single choice)
     */
    case RADIO = 'radio';

    /**
     * Checkbox selection (multiple choices)
     */
    case CHECKBOX = 'checkbox';

    /**
     * Dropdown selection (single choice in dropdown format)
     */
    case DROPDOWN = 'dropdown';

    /**
     * Free text input
     */
    case TEXT = 'text';

    /**
     * Numeric input
     */
    case NUMBER = 'number';

    /**
     * Matrix/grid question (rows and columns)
     */
    case MATRIX = 'matrix';

    /**
     * Rating scale (e.g., 1-5 stars)
     */
    case SCALE = 'scale';

    /**
     * Get display name for question type
     */
    public function label(): string
    {
        return match($this) {
            self::RADIO => 'Pilihan Tunggal',
            self::CHECKBOX => 'Pilihan Ganda',
            self::DROPDOWN => 'Dropdown',
            self::TEXT => 'Teks Bebas',
            self::NUMBER => 'Angka',
            self::MATRIX => 'Matriks',
            self::SCALE => 'Skala Rating',
        };
    }

    /**
     * Check if question type supports multiple answers
     */
    public function supportsMultiple(): bool
    {
        return in_array($this, [
            self::CHECKBOX,
            self::MATRIX,
        ]);
    }
}
