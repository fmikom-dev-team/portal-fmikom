<?php

namespace App\Models\Auth;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthSession extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'device_id',
        'session_token',
        'ip_address',
        'user_agent',
        'geolocation',
        'is_revoked',
        'risk_score',
        'expires_at',
        'last_activity_at',
    ];

    protected $casts = [
        'geolocation' => 'array',
        'is_revoked' => 'boolean',
        'expires_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(AuthDevice::class, 'device_id');
    }
}
