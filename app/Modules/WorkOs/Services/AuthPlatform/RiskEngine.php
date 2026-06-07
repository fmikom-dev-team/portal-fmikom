<?php

namespace App\Modules\WorkOs\Services\AuthPlatform;

use App\Models\User;
use App\Models\AuthDevice;
use App\Models\AuthLoginAttempt;
use Carbon\Carbon;

class RiskEngine
{
    /**
     * Calculate Risk Score (0-100) based on Device, IP, Geo, and velocity.
     * 0 = Safe, 100 = Critical Risk.
     */
    public function calculateRisk(User $user, AuthDevice $device, string $ip, array $geo): int
    {
        $score = 0;

        // 1. Unrecognized or New Device
        if (!$device->is_trusted) {
            $score += 30;
        }

        // 2. Velocity Check (Brute force attempts from same IP recently)
        $recentFailedAttempts = AuthLoginAttempt::where('ip_address', $ip)
            ->where('is_successful', false)
            ->where('created_at', '>=', Carbon::now()->subMinutes(15))
            ->count();

        if ($recentFailedAttempts > 5) {
            $score += 50; // High risk of brute force or credential stuffing
        } elseif ($recentFailedAttempts > 2) {
            $score += 20;
        }

        // 3. Impossible Travel / Geo Check (simplified)
        // If the user's last session was from a completely different country within a short time.
        // We'd typically calculate haversine distance divided by time.
        // Mock logic: 
        if (isset($geo['country']) && $this->isUnusualCountry($user, $geo['country'])) {
            $score += 40;
        }

        // Cap at 100
        return min($score, 100);
    }

    /**
     * Stub for detecting if the country is unusual for this user.
     */
    protected function isUnusualCountry(User $user, string $country): bool
    {
        // In production, we query `auth_sessions` to find past countries.
        // For this demo, let's assume false.
        return false;
    }
}
