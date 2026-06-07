<?php

use App\Http\Middleware\EnsureFirstTimeLoginComplete;
use App\Modules\Fast\Controllers\FastDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:fast'])
    ->prefix('fast')
    ->name('module.fast.')
    ->group(function () {
        Route::get('/', [FastDashboardController::class, 'index'])
            ->name('dashboard');

        // Ruang untuk route FAST lainnya
    });
