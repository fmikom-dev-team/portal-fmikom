<?php

namespace App\Models\Tracer;

use App\Models\User;
use App\Modules\Trace\Services\TraceCacheService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    protected static function booted(): void
    {
        static::saved(function ($registration) {
            TraceCacheService::forgetDashboardCaches(userId: $registration->user_id);
        });

        static::deleted(function ($registration) {
            TraceCacheService::forgetDashboardCaches(userId: $registration->user_id);
        });
    }

    protected $table = 'event_registrations';

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'registered_at',
        'attended_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'attended_at' => 'datetime',
    ];

    /*
    |-------------------------
    | SCOPES
    |-------------------------
    */

    public function scopeRegistered($query)
    {
        return $query->where('status', 'registered');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
