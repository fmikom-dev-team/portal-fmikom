<?php

use App\Modules\WorkOs\Controllers\Auth\PasskeyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Passkeys (WebAuthn / FIDO2) Routes
|--------------------------------------------------------------------------
| Registration requires authentication.
| Authentication options & verification are public (user has no session yet).
*/

// Passkey Registration (requires existing session)
Route::prefix('auth/passkeys')
    ->name('auth.passkeys.')
    ->middleware(['auth', 'throttle:10,1'])
    ->group(function () {

        // List registered passkeys
        Route::get('/', [PasskeyController::class, 'index'])->name('index');

        // Get challenge options for registering a NEW passkey
        Route::post('/register/options', [PasskeyController::class, 'registrationOptions'])
            ->name('register.options');

        // Verify & save the new passkey credential
        Route::post('/register/verify', [PasskeyController::class, 'register'])
            ->name('register');

        // Remove a registered passkey
        Route::delete('/{passkey}', [PasskeyController::class, 'destroy'])
            ->name('destroy');

        // Update a registered passkey (e.g. rename)
        Route::patch('/{passkey}', [PasskeyController::class, 'update'])
            ->name('update');
    });

// Passkey Authentication (PUBLIC — user has no session during login)
Route::prefix('auth/passkeys')
    ->name('auth.passkeys.')
    ->middleware(['throttle:10,1'])
    ->group(function () {

        // Get challenge options for passkey login
        Route::post('/auth/options', [PasskeyController::class, 'authenticationOptions'])
            ->name('auth.options');

        // Verify assertion and log user in
        Route::post('/auth/verify', [PasskeyController::class, 'authenticate'])
            ->name('auth.verify');
    });
