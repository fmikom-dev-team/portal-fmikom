<?php

use App\Modules\Settings\Controllers\ProfileController;
use App\Modules\Settings\Controllers\SecurityController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])
        ->middleware('throttle:10,1')
        ->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])
        ->middleware('throttle:3,1')
        ->name('profile.destroy');

    Route::post('settings/profile/deletion-request', [ProfileController::class, 'requestDeletion'])
        ->middleware('throttle:5,1')
        ->name('profile.request-deletion');

    Route::post('settings/profile/deletion-request/cancel', [ProfileController::class, 'cancelDeletionRequest'])
        ->name('profile.cancel-deletion');

    Route::get('settings/security', [SecurityController::class, 'edit'])->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    Route::put('settings/email', [SecurityController::class, 'updateEmail'])
        ->middleware('throttle:6,1')
        ->name('user-email.update');

    Route::inertia('settings/appearance', 'settings/Appearance')->name('appearance.edit');
});
