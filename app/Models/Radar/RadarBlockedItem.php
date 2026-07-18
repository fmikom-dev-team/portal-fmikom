<?php

namespace App\Models\Radar;

use Illuminate\Database\Eloquent\Model;

class RadarBlockedItem extends Model
{
    protected $fillable = ['type', 'value', 'reason', 'expires_at'];

    protected $casts = [
        'expires_at' => 'datetime',
    ];
}
