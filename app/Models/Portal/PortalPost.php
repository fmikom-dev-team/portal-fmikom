<?php

namespace App\Models\Portal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalPost extends Model
{
    protected static function booted()
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('portal_featured_posts');
            \Illuminate\Support\Facades\Cache::forget('portal_latest_posts');
        });
        static::deleted(function () {
            \Illuminate\Support\Facades\Cache::forget('portal_featured_posts');
            \Illuminate\Support\Facades\Cache::forget('portal_latest_posts');
        });
    }

    protected $guarded = [];

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_SCHEDULED = 'scheduled';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PortalCategory::class, 'category_id');
    }

    public function comments()
    {
        return $this->hasMany(PortalComment::class, 'post_id');
    }
}
