<?php

namespace App\Models\Audit;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditExport extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'status',
        'format',
        'filters',
        'file_path',
        'total_rows',
        'processed_rows',
        'expires_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
