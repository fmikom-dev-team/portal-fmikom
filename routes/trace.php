<?php

use App\Http\Middleware\EnsureFirstTimeLoginComplete;
use App\Modules\Trace\Controllers\TraceDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:trace'])
    ->prefix('trace')
    ->name('module.trace.')
    ->group(function () {
        Route::get('/', [TraceDashboardController::class, 'index'])
            ->name('dashboard');

        // Ruang untuk route TRACE lainnya
    });
