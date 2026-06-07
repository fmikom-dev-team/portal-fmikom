<?php

use App\Jobs\CleanExpiredSessions;
use App\Jobs\RefreshExpiredOAuthTokens;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Schedule
|--------------------------------------------------------------------------
|
| Enterprise job scheduling for Auth Platform maintenance.
| All jobs run on a single server to prevent duplicate processing.
| Queue: 'auth' — handled by Horizon worker.
|
*/

// Session Garbage Collector — every hour
// Marks expired sessions as revoked, purges old records, removes orphans
Schedule::job(new CleanExpiredSessions)
    ->hourly()
    ->onOneServer()
    ->withoutOverlapping()
    ->name('auth:cleanup-sessions');

// OAuth Token Refresh — every 30 minutes
// Proactively refreshes tokens expiring within 10 minutes
Schedule::job(new RefreshExpiredOAuthTokens)
    ->everyThirtyMinutes()
    ->onOneServer()
    ->withoutOverlapping()
    ->name('auth:refresh-oauth-tokens');

// Pagi Chat Archiver — daily at 02:00
// Moves messages older than 90 days from Hot Storage to Cold Storage
Schedule::command('pagi:chat:archive --days=90')
    ->dailyAt('02:00')
    ->onOneServer()
    ->withoutOverlapping()
    ->name('pagi:chat-archive');
