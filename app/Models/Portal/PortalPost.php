<?php

namespace App\Models\Portal;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Searchable;

class PortalPost extends Model
{
    use Searchable;

    /**
     * Get the indexable data array for the model.
     * Only index lean fields — avoid indexing full HTML content.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt ?? strip_tags((string) substr($this->content ?? '', 0, 300)),
            'category' => $this->category?->name ?? '',
            'status' => $this->status,
            'published_at' => $this->published_at?->timestamp,
        ];
    }

    /**
     * Only index published posts that are already live.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === self::STATUS_PUBLISHED
            && $this->published_at !== null
            && Carbon::parse($this->published_at)->isPast();
    }

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('portal_featured_posts');
            Cache::forget('portal_latest_posts');
        });
        static::deleting(function ($post) {
            $post->comments()->delete();
        });
        static::deleted(function () {
            Cache::forget('portal_featured_posts');
            Cache::forget('portal_latest_posts');
        });
    }

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'category_id',
        'status',
        'published_at',
        'thumbnail_path',
        'user_id',
        'is_featured',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';

    const STATUS_PUBLISHED = 'published';

    const STATUS_SCHEDULED = 'scheduled';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUserAttribute()
    {
        $user = $this->getRelationValue('user');
        if (! $user) {
            return null;
        }

        $settings = Cache::rememberForever('portal_settings', function () {
            return PortalSetting::pluck('value', 'key')->toArray();
        });

        $authorName = $settings['author_name'] ?? null;
        $authorImage = $settings['author_image'] ?? null;

        if ($authorName) {
            $user->setAttribute('name', $authorName);
        }
        if ($authorImage) {
            $user->setAttribute('foto_path', str_replace('/storage/', '', $authorImage));
        }

        return $user;
    }

    public function toArray()
    {
        $array = parent::toArray();

        $settings = Cache::rememberForever('portal_settings', function () {
            return PortalSetting::pluck('value', 'key')->toArray();
        });

        $authorName = $settings['author_name'] ?? null;
        $authorImage = $settings['author_image'] ?? null;

        if (isset($array['user'])) {
            if ($authorName) {
                $array['user']['name'] = $authorName;
            }
            if ($authorImage) {
                $array['user']['foto_path'] = str_replace('/storage/', '', $authorImage);
            }
        }

        return $array;
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
