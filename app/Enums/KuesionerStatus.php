<?php

namespace App\Enums;

/**
 * Enum for questionnaire status
 *
 * Defines the lifecycle states of a questionnaire from creation to closure
 */
enum KuesionerStatus: string
{
    /**
     * Questionnaire is in draft state and not visible to respondents
     */
    case DRAFT = 'draft';

    /**
     * Questionnaire is active and visible to respondents within the date range
     */
    case ACTIVE = 'active';

    /**
     * Questionnaire has been published and responses are being collected
     */
    case PUBLISHED = 'published';

    /**
     * Questionnaire has been closed and no more responses are accepted
     */
    case CLOSED = 'closed';

    /**
     * Questionnaire has been archived
     */
    case ARCHIVED = 'archived';

    /**
     * Get display name for questionnaire status
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::ACTIVE => 'Aktif',
            self::PUBLISHED => 'Dipublikasikan',
            self::CLOSED => 'Ditutup',
            self::ARCHIVED => 'Diarsipkan',
        };
    }

    /**
     * Check if respondents can submit answers in this status
     */
    public function canSubmit(): bool
    {
        return in_array($this, [
            self::ACTIVE,
            self::PUBLISHED,
        ]);
    }

    /**
     * Check if questionnaire can be edited in this status
     */
    public function canEdit(): bool
    {
        return in_array($this, [
            self::DRAFT,
        ]);
    }
}
