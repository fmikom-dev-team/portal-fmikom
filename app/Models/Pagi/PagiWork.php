<?php

namespace App\Models\Pagi;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;

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
        'likes',
        'comments',
    ];

    protected $casts = [
        'content' => 'array',
        'is_published' => 'boolean',
        'likes' => 'array',
        'comments' => 'array',
    ];

    protected static function booted()
    {
        static::saved(function ($work) {
            if ($work->user_id) {
                \Illuminate\Support\Facades\Cache::forget("pagi_public_profile_{$work->user_id}");
            }
        });

        static::deleted(function ($work) {
            if ($work->user_id) {
                \Illuminate\Support\Facades\Cache::forget("pagi_public_profile_{$work->user_id}");
            }
        });
    }

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
}
