<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModuleDashboardController;

Route::middleware(['auth', \App\Http\Middleware\EnsureFirstTimeLoginComplete::class, 'module.context:fast'])
    ->prefix('fast')
    ->name('module.fast.')
    ->group(function () {
        Route::get('/', [ModuleDashboardController::class, 'index'])
             ->defaults('moduleCode', 'fast')
             ->name('dashboard');
             
        // Ruang untuk route FAST lainnya
    });
