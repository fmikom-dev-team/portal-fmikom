<?php

namespace App\Models\Audit;

use App\Models\User;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditSecurityIncident extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'audit_log_id',
        'incident_type',
        'user_id',
        'ip_address',
        'severity',
        'details',
        'mitigation_status',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function auditLog(): BelongsTo
    {
        return $this->belongsTo(AuditLog::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
