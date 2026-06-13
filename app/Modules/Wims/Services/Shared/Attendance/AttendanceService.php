<?php

namespace App\Modules\Wims\Services\Shared\Attendance;

class AttendanceService
{
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    public function validateLocation(
        float $userLat,
        float $userLng,
        ?float $officeLat,
        ?float $officeLng,
        ?float $radius,
    ): array {
        if ($officeLat === null || $officeLng === null || $radius === null || $radius <= 0) {
            return [
                'distance' => null,
                'is_valid' => false,
            ];
        }

        $distance = $this->calculateDistance($userLat, $userLng, $officeLat, $officeLng);

        return [
            'distance' => round($distance, 2),
            'is_valid' => $distance <= $radius,
        ];
    }
}
