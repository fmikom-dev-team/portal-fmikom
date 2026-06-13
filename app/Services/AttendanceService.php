<?php

namespace App\Services;

class AttendanceService
{
    // Pada WIMS, jarak antara posisi mahasiswa dan titik perusahaan dihitung
    // dengan algoritma Haversine agar validasi kehadiran berbasis koordinat tetap konsisten.
    public function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earthRadius = 6371000; // meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    // Hasil perhitungan Haversine dipakai untuk geofencing:
    // presensi valid jika posisi mahasiswa masih berada dalam radius perusahaan.
    public function validateLocation($userLat, $userLng, $officeLat, $officeLng, $radius)
    {
        $distance = $this->calculateDistance($userLat, $userLng, $officeLat, $officeLng);

        return [
            'distance' => $distance,
            'is_valid' => $distance <= $radius
        ];
    }
}
