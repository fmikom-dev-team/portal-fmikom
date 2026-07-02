<?php

namespace App\Modules\Trace\Services;

use Illuminate\Support\Facades\Cache;

class TraceCacheService
{
    public static function forgetDashboardCaches(?int $userId = null, ?int $mitraId = null): void
    {
        foreach ([
            'trace_admin_dashboard_stats',
            'trace_alumni_growth_data',
            'trace_analytics_dashboard',
            'trace_statistics_dashboard',
            'trace_total_alumni',
            'map_total_alumni',
            'map_total_mapped',
            'portal_total_alumni',
            'portal_welcome_alumni_data',
            'portal_welcome_alumni_stats',
        ] as $key) {
            Cache::forget($key);
        }

        if ($userId) {
            Cache::forget("trace_alumni_dashboard_{$userId}");
        }

        if ($mitraId) {
            Cache::forget("trace_mitra_dashboard_{$mitraId}");
        }
    }

    public static function forgetJobCaches(?int $mitraId = null): void
    {
        Cache::forget('trace_job_stats');
        self::forgetDashboardCaches(mitraId: $mitraId);
    }

    public static function forgetQuestionnaireCaches(?int $kuesionerId = null): void
    {
        if ($kuesionerId) {
            Cache::forget("kuesioner_analytics_{$kuesionerId}");
            Cache::forget("kuesioner_live_stats_{$kuesionerId}");
        }

        self::forgetDashboardCaches();
    }
}
