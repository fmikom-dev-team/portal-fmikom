<?php

namespace App\Models\Pagi;

use App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagiWarning extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_id',
        'issued_by',
        'severity',
        'type',
        'reason',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(PagiWork::class, 'work_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
