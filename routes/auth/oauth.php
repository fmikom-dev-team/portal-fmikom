<?php

use Illuminate\Support\Facades\Route;
use App\Modules\WorkOs\Controllers\Auth\OAuthController;

/*
|--------------------------------------------------------------------------
| OAuth Routes — PUBLIC (No Auth Middleware)
|--------------------------------------------------------------------------
| These endpoints MUST remain public because the OAuth callback arrives
| before the user has a session. Protect via:
|   - State validation (CSRF-equivalent for OAuth)
|   - Throttle per IP
|   - Replay attack prevention via one-time cache keys
*/
Route::prefix('auth/oauth')
    ->name('auth.oauth.')
    ->middleware(['throttle:60,1'])
    ->group(function () {

        // Step 1: Redirect user → provider authorization URL
        // Called from frontend: GET /auth/oauth/{provider}/redirect
        Route::get('/{provider}/redirect', [OAuthController::class, 'redirect'])
            ->name('redirect');

        // Step 2: Provider sends user back here with code + state
        // The callback MUST be outside any auth middleware
        Route::get('/{provider}/callback', [OAuthController::class, 'callback'])
            ->name('callback');

        // Deauthorize / disconnect a linked provider (requires auth)
        Route::delete('/{provider}/disconnect', [OAuthController::class, 'disconnect'])
            ->middleware('auth')
            ->name('disconnect');

        // Step 3: Dedicated OAuth registration page for new users
        Route::get('/register', [OAuthController::class, 'registerView'])
            ->name('register.view');

        // Step 4: Store registration for new OAuth users
        Route::post('/register', [OAuthController::class, 'registerStore'])
            ->name('register.store');
    });
