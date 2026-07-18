<?php

namespace App\Models\Portal;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class PortalEvent extends Model
{
    use Searchable;

    protected $table = 'portal_events';

    protected $fillable = [
        'title',
        'organizer',
        'organizer_logo',
        'slug',
        'description',
        'location',
        'start_time',
        'end_time',
        'registration_link',
        'status',
        'published_at',
        'thumbnail',
        'is_paid',
        'price',
        'audience_type',
        'is_quota_limited',
        'quota',
    ];

    protected $casts = [
        'start_time' => 'datetime:Y-m-d H:i:s',
        'end_time' => 'datetime:Y-m-d H:i:s',
        'published_at' => 'datetime:Y-m-d H:i:s',
        'is_paid' => 'boolean',
        'is_quota_limited' => 'boolean',
    ];

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => strip_tags($this->description ?? ''),
            'location' => $this->location ?? '',
            'status' => $this->status ?? '',
            'start_time' => $this->start_time?->timestamp,
        ];
    }

    /**
     * Only index published events.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === 'published';
    }
}
