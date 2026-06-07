<?php

use Illuminate\Support\Facades\Route;
use App\Modules\WorkOs\Controllers\Auth\MFAController;

/*
|--------------------------------------------------------------------------
| MFA (Multi-Factor Authentication) Routes
|--------------------------------------------------------------------------
| Setup routes require authentication.
| Challenge verification is semi-public (user has partial session).
*/

// Authenticated setup & management
Route::prefix('auth/mfa')
    ->name('auth.mfa.')
    ->middleware(['auth'])
    ->group(function () {

        // Get current MFA status for user
        Route::get('/status', [MFAController::class, 'status'])->name('status');

        // Initiate TOTP setup → returns secret + QR code SVG
        Route::post('/setup', [MFAController::class, 'setup'])
            ->middleware('throttle:10,1')
            ->name('setup');

        // Verify TOTP code to activate MFA
        Route::post('/verify', [MFAController::class, 'verify'])
            ->middleware('throttle:5,1')
            ->name('verify');

        // Disable MFA (requires current TOTP code as confirmation)
        Route::delete('/disable', [MFAController::class, 'disable'])
            ->middleware('throttle:5,1')
            ->name('disable');

        // Regenerate backup codes (invalidates old ones)
        Route::post('/backup-codes/regenerate', [MFAController::class, 'regenerateBackupCodes'])
            ->middleware('throttle:3,1')
            ->name('backup-codes.regenerate');
    });

// MFA challenge verification — throttled heavily to prevent brute force
Route::prefix('auth/mfa')
    ->name('auth.mfa.')
    ->middleware(['throttle:5,1'])
    ->group(function () {

        // Verify code during login flow (no full session yet)
        Route::post('/challenge', [MFAController::class, 'challenge'])->name('challenge');

        // Verify backup code during login flow
        Route::post('/backup-code', [MFAController::class, 'verifyBackupCode'])->name('backup-code');
    });
