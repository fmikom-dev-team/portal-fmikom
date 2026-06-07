<?php

use App\Http\Middleware\EnsureFirstTimeLoginComplete;
use App\Modules\Wims\Controllers\WimsDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:wims'])
    ->prefix('wims')
    ->name('module.wims.')
    ->group(function () {
        Route::get('/', [WimsDashboardController::class, 'index'])
            ->name('dashboard');

        // Ruang untuk route WIMS lainnya
    });
