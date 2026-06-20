<?php

namespace App\Models\Pagi;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PagiWorkComment extends Model
{
    protected $table = 'pagi_work_comments';

    protected $fillable = [
        'uuid',
        'work_id',
        'user_id',
        'parent_id',
        'body',
    ];

    public function work(): BelongsTo
    {
        return $this->belongsTo(PagiWork::class, 'work_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<PagiWorkComment, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<PagiWorkComment, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function likesRelation(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'pagi_comment_likes', 'comment_id', 'user_id')->withTimestamps();
    }
}
