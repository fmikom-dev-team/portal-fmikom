<?php

namespace App\Jobs;

use App\Modules\WorkOs\Services\OAuth\TokenRefreshService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * RefreshExpiredOAuthTokens — Horizon Job
 *
 * Runs every 30 minutes via the scheduler.
 * Finds and refreshes all OAuth credentials that are expired or expiring soon.
 *
 * Schedule registration (add to routes/console.php or AppServiceProvider):
 *   Schedule::job(new RefreshExpiredOAuthTokens)->everyThirtyMinutes();
 */
class RefreshExpiredOAuthTokens implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60; // seconds between retries

    public function handle(TokenRefreshService $service): void
    {
        $stats = $service->refreshExpiring();

        logger()->info('OAuth token refresh completed', $stats);
    }
}
