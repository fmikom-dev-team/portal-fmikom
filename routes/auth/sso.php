<?php

use Illuminate\Support\Facades\Route;
use App\Modules\WorkOs\Controllers\Auth\SSOController;

/*
|--------------------------------------------------------------------------
| Enterprise SSO Routes (SAML 2.0 / OIDC)
|--------------------------------------------------------------------------
| ACS endpoint MUST be public — the IdP posts back here before session exists.
| Admin management requires auth + role.
*/

// Public SSO Endpoints (IdP callbacks)
Route::prefix('auth/sso')
    ->name('auth.sso.')
    ->middleware(['throttle:30,1'])
    ->group(function () {

        // SAML2 Service Provider metadata
        Route::get('/saml/metadata', [SSOController::class, 'samlMetadata'])
            ->name('saml.metadata');

        // SAML2 Assertion Consumer Service (IdP posts here after authentication)
        // MUST be public — no auth middleware
        Route::post('/saml/acs', [SSOController::class, 'samlAcs'])
            ->name('saml.acs')
            ->withoutMiddleware(['auth']);

        // SAML Single Logout
        Route::get('/saml/sls', [SSOController::class, 'samlSls'])
            ->name('saml.sls');

        // OIDC callback endpoint
        Route::get('/oidc/callback', [SSOController::class, 'oidcCallback'])
            ->name('oidc.callback');

        // SSO initiation — redirect user to IdP
        Route::get('/{connection}/initiate', [SSOController::class, 'initiate'])
            ->name('initiate');
    });

// Admin SSO Connection Management (requires auth + admin role)
Route::prefix('auth/sso/connections')
    ->name('auth.sso.connections.')
    ->middleware(['auth', 'role:super-admin'])
    ->group(function () {
        Route::get('/', [SSOController::class, 'index'])->name('index');
        Route::post('/', [SSOController::class, 'store'])->name('store');
        Route::patch('/{connection}', [SSOController::class, 'update'])->name('update');
        Route::delete('/{connection}', [SSOController::class, 'destroy'])->name('destroy');
    });
