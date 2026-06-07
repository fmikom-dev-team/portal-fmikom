<?php

namespace App\Models\Radar;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadarDevice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'geolocation'  => 'array',
        'is_trusted'   => 'boolean',
        'is_blocked'   => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
