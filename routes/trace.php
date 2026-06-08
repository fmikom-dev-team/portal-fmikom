<?php

use App\Http\Middleware\EnsureFirstTimeLoginComplete;
use App\Modules\Trace\Controllers\TraceDashboardController;
use App\Modules\Trace\Controllers\Alumni\TraceAlumniProfileController;

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:trace'])
    ->prefix('trace')
    ->name('module.trace.')
    ->group(function () {
        Route::get('/', [TraceDashboardController::class, 'index'])
            ->name('dashboard');
        Route::get('/profile-alumni', [TraceAlumniProfileController::class, 'index'])->name('profile-alumni');
        Route::post('/profile-alumni', [TraceAlumniProfileController::class, 'update'])->name('profile-alumni.update');
        // Ruang untuk route TRACE lainnya
    });
