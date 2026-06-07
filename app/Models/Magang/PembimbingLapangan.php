<?php

namespace App\Models\Magang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PembimbingLapangan extends Model
{
    protected $fillable = ['user_id', 'perusahaan_id', 'jabatan', 'is_active'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(PerusahaanMitra::class, 'perusahaan_id');
    }

    public function pendaftaranMagangs(): HasMany
    {
        return $this->hasMany(PendaftaranMagang::class, 'pembimbing_lapangan_id');
    }

    public function penilaianMagangs(): HasMany
    {
        return $this->hasMany(PenilaianMagang::class, 'pembimbing_lapangan_id');
    }
}