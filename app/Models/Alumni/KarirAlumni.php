<?php

namespace App\Models\Alumni;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KarirAlumni extends Model
{
    protected $fillable = [
        'alumni_id', 'nama_perusahaan', 'jabatan', 'bidang_industri',
        'kota', 'negara', 'latitude', 'longitude', 'tanggal_mulai',
        'tanggal_selesai', 'gaji_range', 'status_kerja', 'is_current',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_current' => 'boolean',
    ];

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(ProfilAlumni::class, 'alumni_id');
    }

    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('is_current', true);
    }

    public function getLokasiGeoJson(): array
    {
        if (! $this->latitude || ! $this->longitude) {
            return [];
        }

        return [
            'type' => 'Point',
            'coordinates' => [$this->longitude, $this->latitude],
        ];
    }
}
