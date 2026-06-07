<?php

namespace App\Models\Pagi;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagiReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_id',
        'reporter_id',
        'reason',
        'description',
        'status',
        'reviewed_by',
        'admin_note',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function work(): BelongsTo
    {
        return $this->belongsTo(PagiWork::class, 'work_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
