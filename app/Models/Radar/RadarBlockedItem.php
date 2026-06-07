<?php

namespace App\Models\Radar;

use Illuminate\Database\Eloquent\Model;

class RadarBlockedItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
