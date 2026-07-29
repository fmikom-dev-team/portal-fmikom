<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\WorkOsAlert;
use Illuminate\Support\Facades\Log;

class SystemAlertService
{
    /**
     * Log a real-time system alert notification to all admin users.
     */
    public static function log(string $title, string $description, string $severity = 'error', array $extra = []): void
    {
        try {
            $admins = User::query()
                ->where('is_super_admin', true)
                ->orWhere('is_admin', true)
                ->orWhereIn('role', ['super_admin', 'admin'])
                ->get();

            foreach ($admins as $admin) {
                $admin->notify(new WorkOsAlert($title, $description, $severity, $extra));
            }
        } catch (\Throwable $e) {
            Log::error('Failed to record SystemAlert: '.$e->getMessage());
        }
    }
}
