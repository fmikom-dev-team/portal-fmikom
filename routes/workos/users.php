<?php

use Illuminate\Support\Facades\Route;
use App\Modules\WorkOs\Controllers\UsersController;

/*
|--------------------------------------------------------------------------
| WorkOS Users Management
|--------------------------------------------------------------------------
*/
Route::prefix('users')->name('users.')->group(function () {
    Route::post('/', [UsersController::class, 'store'])->name('store');
    Route::get('/template', [UsersController::class, 'template'])->name('template');
    Route::post('/upload', [UsersController::class, 'upload'])->name('upload');
    Route::patch('/{user}', [UsersController::class, 'update'])->name('update');
    Route::delete('/{user}', [UsersController::class, 'destroy'])->name('destroy');
    Route::post('/{user}/approve', [UsersController::class, 'approve'])->name('approve');
    Route::post('/{user}/reject', [UsersController::class, 'reject'])->name('reject');
    Route::patch('/{user}/assign-role', [UsersController::class, 'assignRole'])->name('assign-role');
    Route::post('/{user}/module-roles', [UsersController::class, 'addModuleRole'])->name('module-roles.store');
    Route::delete('/{user}/oauth/{credential}', [UsersController::class, 'disconnectOAuth'])->name('oauth.disconnect');

    // User sessions management routes
    Route::get('/{user}/sessions', [UsersController::class, 'sessions'])->name('sessions');
    Route::delete('/{user}/sessions', [UsersController::class, 'revokeAllSessions'])->name('sessions.revoke-all');
    Route::delete('/{user}/sessions/clear', [UsersController::class, 'clearInactiveSessions'])->name('sessions.clear');
    Route::delete('/{user}/sessions/{sessionId}', [UsersController::class, 'revokeSession'])->name('sessions.revoke');

    // User emails log route
    Route::get('/{user}/emails', [UsersController::class, 'emails'])->name('emails');
    Route::delete('/{user}/emails/clear', [UsersController::class, 'clearEmailHistory'])->name('emails.clear');
});

Route::prefix('module-roles')->name('module-roles.')->group(function () {
    Route::patch('/{moduleRole}', [UsersController::class, 'updateModuleRole'])->name('update');
    Route::delete('/{moduleRole}', [UsersController::class, 'removeModuleRole'])->name('destroy');
});
