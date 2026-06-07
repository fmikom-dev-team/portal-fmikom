<?php

namespace App\Models\Radar;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RadarProtection extends Model
{
    protected $guarded = [];

    protected $casts = [
        'threshold_config' => 'array',
        'auto_block' => 'boolean',
        'notify_admin' => 'boolean',
    ];

    public function detections(): HasMany
    {
        return $this->hasMany(RadarDetection::class, 'radar_protection_id');
    }
}
