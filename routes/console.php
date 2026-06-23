<?php

use App\Jobs\CleanExpiredSessions;
use App\Jobs\RefreshExpiredOAuthTokens;
use Illuminate\Support\Facades\Schedule;
use Laravel\Pulse\Facades\Pulse;

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

// Telescope Database Pruner — daily at 03:00
// Deletes Telescope entries older than 24 hours
Schedule::command('telescope:prune --hours=24')
    ->dailyAt('03:00')
    ->onOneServer()
    ->withoutOverlapping()
    ->name('telescope:prune');

// Pulse Database Trimmer — daily at 03:30
// Trims Pulse storage based on PULSE_STORAGE_KEEP setting
Schedule::call(fn () => Pulse::trim())
    ->dailyAt('03:30')
    ->name('pulse:trim')
    ->onOneServer();

// Clean old temporary files (WebP/WebM) created by upload middleware/traits
Schedule::call(function () {
    $tempDir = sys_get_temp_dir();
    $files = glob($tempDir.'/webp*');
    $files = array_merge($files, glob($tempDir.'/webm*'));
    $now = time();
    $retention = 86400; // Keep for 24 hours
    foreach ($files as $file) {
        if (is_file($file) && ($now - filemtime($file)) > $retention) {
            @unlink($file);
        }
    }
})->dailyAt('04:00')->name('sys:cleanup-temp-uploads');

/*
|--------------------------------------------------------------------------
| Trace Module — Email Digest & Reminders
|--------------------------------------------------------------------------
|
| Scheduled emails for the Alumni Tracer module.
| Weekly digest, event reminders, and kuesioner reminders.
|
*/

// Weekly Digest — every Monday at 08:00
// Sends a summary of new job listings & events from the past week
Schedule::command('trace:weekly-digest')
    ->weeklyOn(1, '08:00')
    ->onOneServer()
    ->withoutOverlapping()
    ->name('trace:weekly-digest');

// Event Reminder — daily at 08:00
// Notifies registered users about events happening tomorrow
Schedule::command('trace:event-reminder')
    ->dailyAt('08:00')
    ->onOneServer()
    ->withoutOverlapping()
    ->name('trace:event-reminder');

// Kuesioner Reminder — every Wednesday at 09:00
// Reminds alumni who haven't filled out active kuesioners
Schedule::command('trace:kuesioner-reminder')
    ->weeklyOn(3, '09:00')
    ->onOneServer()
    ->withoutOverlapping()
    ->name('trace:kuesioner-reminder');
