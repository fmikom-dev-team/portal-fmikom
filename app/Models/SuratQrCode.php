<?php
// app/Models/SuratQrCode.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratQrCode extends Model
{
    protected $fillable = [
        'surat_id',
        'token',
        'status',
        'revoked_reason',
        'revoked_by',
        'revoked_at',
        'activated_at',
    ];

    protected $casts = [
        'revoked_at'   => 'datetime',
        'activated_at' => 'datetime',
    ];

    const STATUS_ACTIVE  = 'active';
    const STATUS_REVOKED = 'revoked';
    const STATUS_EXPIRED = 'expired';

    // ── Relationships ─────────────────────────────────────────────
    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    // ── Helpers ───────────────────────────────────────────────────
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function revoke(int $userId, string $reason = ''): void
    {
        $this->update([
            'status'         => self::STATUS_REVOKED,
            'revoked_by'     => $userId,
            'revoked_at'     => now(),
            'revoked_reason' => $reason,
        ]);
    }
}
