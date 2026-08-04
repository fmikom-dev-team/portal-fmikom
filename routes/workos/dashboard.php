<?php

use App\Modules\WorkOs\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WorkOS Dashboard — Main SPA Entry Point
|--------------------------------------------------------------------------
*/
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/instant-search', [DashboardController::class, 'instantSearch'])->name('instant-search');

Route::post('/emails/config', [DashboardController::class, 'updateMailConfig'])->name('emails.config.update');

Route::post('/emails/test-send', [DashboardController::class, 'sendRealTestEmail'])->name('emails.test-send');

Route::post('/settings/update', [DashboardController::class, 'updateSystemSettings'])->name('settings.update');
Route::get('/settings/helpdesk', [DashboardController::class, 'getHelpdeskSetting'])->name('settings.helpdesk');
Route::post('/settings/flush-cache', [DashboardController::class, 'flushSystemCache'])->name('settings.flush-cache');

Route::get('/settings/sitelinks', [DashboardController::class, 'getSitelinks'])->name('settings.sitelinks.index');
Route::post('/settings/sitelinks', [DashboardController::class, 'storeSitelink'])->name('settings.sitelinks.store');
Route::put('/settings/sitelinks/{id}', [DashboardController::class, 'updateSitelink'])->name('settings.sitelinks.update');
Route::delete('/settings/sitelinks/{id}', [DashboardController::class, 'destroySitelink'])->name('settings.sitelinks.destroy');

Route::post('/invitations/send', [DashboardController::class, 'sendInvitation'])->name('invitations.send');
Route::post('/invitations/{id}/resend', [DashboardController::class, 'resendInvitation'])->name('invitations.resend');
Route::delete('/invitations/{id}', [DashboardController::class, 'revokeInvitation'])->name('invitations.revoke');

Route::post('/notifications/mark-all-read', [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.mark-all-read');
Route::post('/notifications/clear', [DashboardController::class, 'clearNotifications'])->name('notifications.clear');
Route::post('/notifications/{id}/toggle-read', [DashboardController::class, 'toggleNotificationRead'])->name('notifications.toggle-read');
Route::delete('/notifications/{id}', [DashboardController::class, 'destroyNotification'])->name('notifications.destroy');

Route::post('/emails/logs/clear', [DashboardController::class, 'clearEmailLogs'])->name('emails.logs.clear');
Route::post('/webhooks/save', [DashboardController::class, 'saveWebhookConfig'])->name('webhooks.save');
Route::post('/webhooks/redeliver/{id}', [DashboardController::class, 'redeliverWebhookEvent'])->name('webhooks.redeliver');

// SPA catch-all: every sub-path (e.g. /workos/organizations, /workos/users) returns
// the same Dashboard page — client-side navigation handles the rest
Route::get('/{any}', [DashboardController::class, 'index'])
    ->where('any', '.*')
    ->name('spa');
