<?php
// app/Models/SuratHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratHistory extends Model
{
    protected $fillable = [
        'surat_id',
        'user_id',
        'action',
        'action_label',
        'keterangan',
        'meta',
        'ip_address',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    // ── Action constants ──────────────────────────────────────────
    const ACTION_CREATED    = 'created';
    const ACTION_SUBMITTED  = 'submitted';
    const ACTION_VALIDATED  = 'validated';
    const ACTION_APPROVED   = 'approved';
    const ACTION_REJECTED   = 'rejected';
    const ACTION_GENERATED  = 'generated';
    const ACTION_PRINTED    = 'printed';
    const ACTION_QR_SCANNED = 'qr_scanned';
    const ACTION_QR_REVOKED = 'qr_revoked';
    const ACTION_REVISED    = 'revised';

    // ── Relationships ─────────────────────────────────────────────
    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
