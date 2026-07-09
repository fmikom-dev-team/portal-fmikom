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
        'activated_at',
    ];

    protected $casts = [
        'activated_at' => 'datetime',
    ];

    const STATUS_ACTIVE = 'active';

    const STATUS_REVOKED = 'revoked';

    const STATUS_EXPIRED = 'expired';

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
