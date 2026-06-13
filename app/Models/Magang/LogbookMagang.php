<?php

namespace App\Models\Magang;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogbookMagang extends Model
{
    protected $fillable = [
        'pendaftaran_id', 'tanggal', 'jam_mulai', 'jam_selesai',
        'aktivitas_harian', 'kompetensi_dicapai', 'catatan_mitra',
        'reviewed_by_mitra_user_id', 'reviewed_by_mitra_at',
        'catatan_dosen', 'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'reviewed_by_mitra_at' => 'datetime',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranMagang::class, 'pendaftaran_id');
    }

    public function scopeByBulan(Builder $query, $bulan, $tahun): Builder
    {
        return $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
    }

    public function reviewedByMitra(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by_mitra_user_id');
    }
}
