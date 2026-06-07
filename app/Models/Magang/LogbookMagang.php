<?php

namespace App\Models\Magang;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogbookMagang extends Model
{
    protected $fillable = [
        'pendaftaran_id', 'tanggal', 'aktivitas_harian',
        'kompetensi_dicapai', 'catatan_dosen',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function pendaftaran(): BelongsTo
    {
        return $this->belongsTo(PendaftaranMagang::class, 'pendaftaran_id');
    }

    public function scopeByBulan(Builder $query, $bulan, $tahun): Builder
    {
        return $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
    }
}
