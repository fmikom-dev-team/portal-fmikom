<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModuleDashboardController;

Route::middleware(['auth', \App\Http\Middleware\EnsureFirstTimeLoginComplete::class, 'module.context:wims'])
    ->prefix('wims')
    ->name('module.wims.')
    ->group(function () {
        Route::get('/', [ModuleDashboardController::class, 'index'])
             ->defaults('moduleCode', 'wims')
             ->name('dashboard');
             
        // Ruang untuk route WIMS lainnya
    });
