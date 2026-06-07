<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Fast\Controllers\FastDashboardController;

Route::middleware(['auth', \App\Http\Middleware\EnsureFirstTimeLoginComplete::class, 'module.context:fast'])
    ->prefix('fast')
    ->name('module.fast.')
    ->group(function () {
        Route::get('/', [FastDashboardController::class, 'index'])
             ->name('dashboard');
             
        // Ruang untuk route FAST lainnya
    });
