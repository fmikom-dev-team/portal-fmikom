<?php

namespace App\Models\Radar;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadarSecurityEvent extends Model
{
    protected $guarded = [];

    protected $casts = [
        'event_data' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
