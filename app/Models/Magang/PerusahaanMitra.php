<?php

namespace App\Models\Magang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PerusahaanMitra extends Model
{
    protected $fillable = [
        'nama', 'alamat', 'kota', 'latitude', 'longitude',
        'radius_valid_meter', 'bidang_industri', 'kontak_person',
        'telepon', 'email',
    ];

    public function pembimbingLapangans(): HasMany
    {
        return $this->hasMany(PembimbingLapangan::class, 'perusahaan_id');
    }

    public function pendaftaranMagangs(): HasMany
    {
        return $this->hasMany(PendaftaranMagang::class, 'perusahaan_id');
    }

    public function isWithinRadius($lat, $lng): bool
    {
        return $this->distanceTo($lat, $lng) <= $this->radius_valid_meter;
    }

    public function distanceTo($lat, $lng): float
    {
        // Simple Haversine approximation
        $earthRadius = 6371000;
        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($lat);
        $lonTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}
