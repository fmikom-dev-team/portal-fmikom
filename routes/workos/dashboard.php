<?php

use App\Modules\WorkOs\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WorkOS Dashboard — Main SPA Entry Point
|--------------------------------------------------------------------------
*/
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::post('/emails/config', [DashboardController::class, 'updateMailConfig'])->name('emails.config.update');
Route::post('/emails/test-send', [DashboardController::class, 'sendRealTestEmail'])->name('emails.test-send');

Route::post('/settings/update', [DashboardController::class, 'updateSystemSettings'])->name('settings.update');
Route::post('/settings/flush-cache', [DashboardController::class, 'flushSystemCache'])->name('settings.flush-cache');

Route::post('/notifications/mark-all-read', [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.mark-all-read');
Route::post('/notifications/clear', [DashboardController::class, 'clearNotifications'])->name('notifications.clear');
Route::post('/notifications/{id}/toggle-read', [DashboardController::class, 'toggleNotificationRead'])->name('notifications.toggle-read');

// SPA catch-all: every sub-path (e.g. /workos/organizations, /workos/users) returns
// the same Dashboard page — client-side navigation handles the rest
Route::get('/{any}', [DashboardController::class, 'index'])
    ->where('any', '.*')
    ->name('spa');
