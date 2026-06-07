<?php

namespace App\Models\Pagi;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PagiTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'usage_count',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function works(): BelongsToMany
    {
        return $this->belongsToMany(PagiWork::class, 'pagi_work_tags', 'tag_id', 'work_id');
    }
}
