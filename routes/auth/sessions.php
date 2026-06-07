<?php

use App\Modules\WorkOs\Controllers\Auth\SessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Session Routes — Requires Authentication
|--------------------------------------------------------------------------
*/
Route::prefix('auth/sessions')
    ->name('auth.sessions.')
    ->middleware(['auth', 'secure.session'])
    ->group(function () {

        // List all active sessions for the authenticated user
        Route::get('/', [SessionController::class, 'index'])->name('index');

        // Revoke a specific session by ID
        Route::delete('/{session}', [SessionController::class, 'revoke'])->name('revoke');

        // Revoke ALL other sessions (keep current)
        Route::delete('/', [SessionController::class, 'revokeOthers'])->name('revoke-others');

        // Revoke all sessions (full logout everywhere)
        Route::post('/revoke-all', [SessionController::class, 'revokeAll'])->name('revoke-all');
    });
