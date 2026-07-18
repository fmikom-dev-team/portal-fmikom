<?php

namespace App\Models\Radar;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadarDetection extends Model
{
    protected $fillable = [
        'user_id',
        'radar_protection_id',
        'radar_device_id',
        'detection_type',
        'severity',
        'ip_address',
        'request_uri',
        'user_agent',
        'metadata',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'metadata' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function protection(): BelongsTo
    {
        return $this->belongsTo(RadarProtection::class, 'radar_protection_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(RadarDevice::class, 'radar_device_id');
    }
}
