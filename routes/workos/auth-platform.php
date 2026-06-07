<?php

use Illuminate\Support\Facades\Route;
use App\Modules\WorkOs\Controllers\AuthenticationController;
use App\Modules\WorkOs\Controllers\SecurityController;

/*
|--------------------------------------------------------------------------
| WorkOS Auth Platform Admin Dashboard
|--------------------------------------------------------------------------
| ADMIN management routes for the authentication platform.
| Public auth flows (OAuth callbacks, passkeys login) → routes/auth/*.php
*/
Route::prefix('auth-platform')->name('auth-platform.')->group(function () {

    // ── Analytics ──────────────────────────────────────────────────────────
    Route::get('/analytics', [AuthenticationController::class, 'analytics'])->name('analytics');
    Route::post('/analytics/clear', [AuthenticationController::class, 'clearAnalytics'])->name('analytics.clear');

    // ── Login Methods ──────────────────────────────────────────────────────
    Route::get('/methods', [AuthenticationController::class, 'methods'])->name('methods');
    Route::patch('/methods', [AuthenticationController::class, 'updateMethod'])->name('methods.update');

    // ── Features ───────────────────────────────────────────────────────────────
    Route::get('/features', [AuthenticationController::class, 'features'])->name('features');
    Route::patch('/features', [AuthenticationController::class, 'updateFeature'])->name('features.update');

    // ── OAuth Providers ────────────────────────────────────────────────────
    Route::get('/providers', [AuthenticationController::class, 'providers'])->name('providers');
    Route::patch('/providers/{slug}', [AuthenticationController::class, 'updateProvider'])->name('providers.update');

    // ── Sessions ───────────────────────────────────────────────────────────
    Route::get('/sessions', [AuthenticationController::class, 'sessions'])->name('sessions');
    Route::delete('/sessions/all', [AuthenticationController::class, 'revokeAllSessions'])->name('sessions.revoke-all');
    Route::delete('/sessions/{session}', [AuthenticationController::class, 'revokeSession'])->name('sessions.revoke');
    Route::get('/sessions-config', [AuthenticationController::class, 'sessionsConfig'])->name('sessions.config');
    Route::patch('/sessions-config', [AuthenticationController::class, 'updateSessionsConfig'])->name('sessions.config.update');
    Route::post('/sessions/jwt-preview', [AuthenticationController::class, 'previewJwt'])->name('sessions.jwt-preview');

    // ── MFA ────────────────────────────────────────────────────────────────
    Route::get('/mfa/status', [AuthenticationController::class, 'mfaStatus'])->name('mfa.status');
    Route::get('/mfa/user-status', [SecurityController::class, 'userMfaStatus'])->name('mfa.user-status');
    Route::post('/mfa/setup', [SecurityController::class, 'setupMfa'])->name('mfa.setup');
    Route::post('/mfa/verify', [SecurityController::class, 'verifyMfa'])->name('mfa.verify');

    // ── Passkeys ───────────────────────────────────────────────────────────
    Route::post('/passkeys/options', [SecurityController::class, 'registerPasskeyOptions'])->name('passkeys.options');
    Route::post('/passkeys/verify', [SecurityController::class, 'verifyPasskeyRegistration'])->name('passkeys.verify');

    // ── Password Policies ──────────────────────────────────────────────────
    Route::get('/password-policies', [AuthenticationController::class, 'passwordPolicies'])->name('password-policies');
    Route::patch('/password-policies', [AuthenticationController::class, 'updatePasswordPolicy'])->name('password-policies.update');

    // ── Audit Logs ─────────────────────────────────────────────────────────
    Route::get('/audit-logs', [AuthenticationController::class, 'auditLogs'])->name('audit-logs');

    // ── Analytics Item Clear/Delete ────────────────────────────────────────
    Route::delete('/analytics/failed-logins/{attempt}', [AuthenticationController::class, 'deleteFailedLogin'])->name('analytics.failed-logins.delete');
    Route::delete('/analytics/users/{user}', [AuthenticationController::class, 'deleteUser'])->name('analytics.users.delete');

    // ── OAuth Admin Redirect ───────────────────────────────────────────────
    Route::post('/oauth/redirect/{provider}', [\App\Modules\WorkOs\Controllers\OAuthController::class, 'redirect'])
        ->name('oauth.redirect');
});
