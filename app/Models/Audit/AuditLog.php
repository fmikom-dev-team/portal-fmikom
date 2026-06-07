<?php

namespace App\Models\Audit;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory, HasUuids;

    // By default, Laravel expects updated_at to exist unless we disable it
    const UPDATED_AT = null;

    protected $fillable = [
        'event_type',
        'severity',
        'actor_id',
        'organization_id',
        'target_type',
        'target_id',
        'ip_address',
        'user_agent',
        'device_info',
        'request_method',
        'request_path',
        'response_status',
        'correlation_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the user who performed the action.
     */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Get the target entity that was affected.
     */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }
}
