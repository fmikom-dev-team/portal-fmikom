<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Trace\Controllers\TraceDashboardController;

Route::middleware(['auth', \App\Http\Middleware\EnsureFirstTimeLoginComplete::class, 'module.context:trace'])
    ->prefix('trace')
    ->name('module.trace.')
    ->group(function () {
        Route::get('/', [TraceDashboardController::class, 'index'])
             ->name('dashboard');
             
        // Ruang untuk route TRACE lainnya
    });
