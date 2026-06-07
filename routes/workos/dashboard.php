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

// SPA catch-all: every sub-path (e.g. /workos/organizations, /workos/users) returns
// the same Dashboard page — client-side navigation handles the rest
Route::get('/{any}', [DashboardController::class, 'index'])
    ->where('any', '.*')
    ->name('spa');
