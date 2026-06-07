<?php

namespace App\Jobs;

use App\Modules\WorkOs\Services\Sessions\SessionCleanupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * CleanExpiredSessions — Horizon Job
 *
 * Schedule registration (routes/console.php):
 *
 *   use App\Jobs\CleanExpiredSessions;
 *   Schedule::job(new CleanExpiredSessions)->hourly()->onOneServer();
 */
class CleanExpiredSessions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function handle(SessionCleanupService $service): void
    {
        $stats = $service->run();

        logger()->info('Session cleanup job completed', $stats);
    }
}
