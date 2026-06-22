<?php

namespace App\Models\Pagi;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * @method bool|null delete()
 */
class PagiWork extends Model
{
    protected $table = 'pagi_works';

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'cover_image',
        'is_published',
        'visibility',
        'status',
        'views_count',
        'description',
        'category',
        'tools_used',
    ];

    protected $casts = [
        'content' => 'array',
        'is_published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function ($work) {
            if ($work->user_id) {
                Cache::forget("pagi_public_profile_{$work->user_id}");
            }
            // Invalidate admin stats cache when a work changes
            Cache::forget('pagi_admin_stats');
            Cache::forget('pagi_admin_moderation');
            foreach (['7d', '30d', '90d'] as $range) {
                Cache::forget("pagi_admin_chart_{$range}");
            }
            static::clearPagiPublicCaches();
        });

        static::deleted(function ($work) {
            if ($work->user_id) {
                Cache::forget("pagi_public_profile_{$work->user_id}");
            }
            Cache::forget('pagi_admin_stats');
            Cache::forget('pagi_admin_moderation');
            foreach (['7d', '30d', '90d'] as $range) {
                Cache::forget("pagi_admin_chart_{$range}");
            }
            static::clearPagiPublicCaches();
        });
    }

    protected static function clearPagiPublicCaches(): void
    {
        Cache::forget('pagi_feed_projects_raw');
        for ($i = 1; $i <= 5; $i++) {
            Cache::forget("pagi_gallery_recommended_page_{$i}");
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Relations
    // ──────────────────────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(PagiTag::class, 'pagi_work_tags', 'work_id', 'tag_id');
    }

    public function reports()
    {
        return $this->hasMany(PagiReport::class, 'work_id');
    }

    public function warnings()
    {
        return $this->hasMany(PagiWarning::class, 'work_id');
    }

    public function likesRelation(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pagi_work_likes', 'work_id', 'user_id')->withTimestamps();
    }

    public function commentsRelation(): HasMany
    {
        return $this->hasMany(PagiWorkComment::class, 'work_id');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Dynamic accessors
    //
    // These accessors check whether the relation has already been eager-loaded
    // (via ->with('likesRelation') or ->with('commentsRelation.xxx')).
    // If it has, we reuse the in-memory collection instead of firing a new query.
    // This eliminates the N+1 problem when iterating over a collection of works.
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Returns an array of user IDs who liked this work.
     *
     * Safe to call even inside a ->map() loop provided you pre-loaded with:
     *   ->with('likesRelation')
     */
    public function getLikesAttribute(): array
    {
        if ($this->relationLoaded('likesRelation')) {
            return $this->likesRelation->pluck('id')->toArray();
        }

        // Fallback: single targeted query (used when only one work is fetched)
        return $this->likesRelation()->pluck('user_id')->toArray();
    }

    /**
     * Returns a formatted array of comments (with replies and likes) for this work.
     *
     * Safe to call inside a ->map() loop provided you pre-loaded with:
     *   ->with([
     *       'commentsRelation' => fn ($q) => $q->whereNull('parent_id'),
     *       'commentsRelation.user:id,name,pagi_username,foto_path',
     *       'commentsRelation.likesRelation',
     *       'commentsRelation.replies.user:id,name,pagi_username,foto_path',
     *       'commentsRelation.replies.likesRelation',
     *   ])
     */
    public function getCommentsAttribute(): array
    {
        if ($this->relationLoaded('commentsRelation')) {
            // Use the in-memory collection (no extra query)
            $topLevel = $this->commentsRelation->filter(fn ($c) => is_null($c->parent_id));

            return $topLevel->map(function ($c) {
                $avatar = $c->user && $c->user->foto_path
                    ? (str_starts_with($c->user->foto_path, 'http')
                        ? $c->user->foto_path
                        : asset('storage/'.$c->user->foto_path))
                    : null;

                $replies = ($c->relationLoaded('replies') ? $c->replies : collect())->map(function ($r) {
                    $rAvatar = $r->user && $r->user->foto_path
                        ? (str_starts_with($r->user->foto_path, 'http')
                            ? $r->user->foto_path
                            : asset('storage/'.$r->user->foto_path))
                        : null;

                    return [
                        'id' => $r->uuid,
                        'user_id' => $r->user_id,
                        'name' => $r->user->name ?? 'Anonymous',
                        'avatar' => $rAvatar,
                        'body' => $r->body,
                        'content' => $r->body,
                        'created_at' => $r->created_at->toISOString(),
                        'time' => $r->created_at->diffForHumans(),
                        'likes' => $r->relationLoaded('likesRelation')
                            ? $r->likesRelation->pluck('id')->toArray()
                            : [],
                    ];
                })->toArray();

                return [
                    'id' => $c->uuid,
                    'user_id' => $c->user_id,
                    'name' => $c->user->name ?? 'Anonymous',
                    'pagi_username' => $c->user?->pagi_username,
                    'avatar' => $avatar,
                    'body' => $c->body,
                    'content' => $c->body,
                    'created_at' => $c->created_at->toISOString(),
                    'time' => $c->created_at->diffForHumans(),
                    'likes' => $c->relationLoaded('likesRelation')
                        ? $c->likesRelation->pluck('id')->toArray()
                        : [],
                    'replies' => $replies,
                ];
            })->values()->toArray();
        }

        // Fallback: query with full eager-load (used when a single work is fetched fresh)
        $comments = $this->commentsRelation()
            ->whereNull('parent_id')
            ->with([
                'user:id,name,pagi_username,foto_path',
                'likesRelation',
                'replies.user:id,name,pagi_username,foto_path',
                'replies.likesRelation',
            ])
            ->get();

        return $comments->map(function ($c) {
            $avatar = $c->user && $c->user->foto_path
                ? (str_starts_with($c->user->foto_path, 'http')
                    ? $c->user->foto_path
                    : asset('storage/'.$c->user->foto_path))
                : null;

            $replies = $c->replies->map(function ($r) {
                $rAvatar = $r->user && $r->user->foto_path
                    ? (str_starts_with($r->user->foto_path, 'http')
                        ? $r->user->foto_path
                        : asset('storage/'.$r->user->foto_path))
                    : null;

                return [
                    'id' => $r->uuid,
                    'user_id' => $r->user_id,
                    'name' => $r->user->name ?? 'Anonymous',
                    'avatar' => $rAvatar,
                    'body' => $r->body,
                    'content' => $r->body,
                    'created_at' => $r->created_at->toISOString(),
                    'time' => $r->created_at->diffForHumans(),
                    'likes' => $r->likesRelation->pluck('id')->toArray(),
                ];
            })->toArray();

            return [
                'id' => $c->uuid,
                'user_id' => $c->user_id,
                'name' => $c->user->name ?? 'Anonymous',
                'pagi_username' => $c->user?->pagi_username,
                'avatar' => $avatar,
                'body' => $c->body,
                'content' => $c->body,
                'created_at' => $c->created_at->toISOString(),
                'time' => $c->created_at->diffForHumans(),
                'likes' => $c->likesRelation->pluck('id')->toArray(),
                'replies' => $replies,
            ];
        })->toArray();
    }
}
