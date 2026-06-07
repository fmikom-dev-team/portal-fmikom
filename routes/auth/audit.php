<?php

use Illuminate\Support\Facades\Route;
use App\Modules\WorkOs\Controllers\Auth\AuditController;

/*
|--------------------------------------------------------------------------
| Auth Audit Log Routes — Admin Only
|--------------------------------------------------------------------------
*/
Route::prefix('auth/audit')
    ->name('auth.audit.')
    ->middleware(['auth', 'role:super-admin'])
    ->group(function () {

        Route::get('/events', [AuditController::class, 'events'])->name('events');
        Route::get('/login-attempts', [AuditController::class, 'loginAttempts'])->name('login-attempts');
        Route::get('/security', [AuditController::class, 'security'])->name('security');
    });
