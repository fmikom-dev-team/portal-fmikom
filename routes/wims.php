<?php
use Illuminate\Support\Facades\Route;
use App\Modules\Wims\Controllers\WimsDashboardController;

Route::middleware(['auth', \App\Http\Middleware\EnsureFirstTimeLoginComplete::class, 'module.context:wims'])
    ->prefix('wims')
    ->name('module.wims.')
    ->group(function () {
        Route::get('/', [WimsDashboardController::class, 'index'])
             ->name('dashboard');
             
        // Ruang untuk route WIMS lainnya
    });
