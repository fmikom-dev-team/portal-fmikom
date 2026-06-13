<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class LogbookMagang extends Model
{
    protected $fillable = [
        'pendaftaran_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'aktivitas_harian',
        'kompetensi_dicapai',
        'catatan_mitra',
        'reviewed_by_mitra_user_id',
        'reviewed_by_mitra_at',
        'catatan_dosen',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'reviewed_by_mitra_at' => 'datetime',
        ];
    }

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranMagang::class, 'pendaftaran_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(LogbookPhoto::class, 'logbook_id');
    }

    public function reviewedByMitra(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_mitra_user_id');
    }
}
