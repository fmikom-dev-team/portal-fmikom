<?php

use App\Modules\WorkOs\Controllers\Auth\MagicLinkController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Magic Link Routes
|--------------------------------------------------------------------------
| Send is throttled. Verify uses signed URLs to prevent forgery.
*/

// Request a magic link (semi-public, throttled)
Route::post('/auth/magic-link/send', [MagicLinkController::class, 'send'])
    ->middleware(['throttle:3,5'])
    ->name('auth.magic-link.send');

// Verify via signed URL — MUST be public and signed
Route::get('/auth/magic-link/verify', [MagicLinkController::class, 'verify'])
    ->middleware(['signed', 'throttle:10,1'])
    ->name('auth.magic-link.verify');
