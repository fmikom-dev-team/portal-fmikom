<?php

namespace App\Models\Portal;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class PortalPost extends Model
{
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

    protected $guarded = [];

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
