<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModuleDashboardController;

Route::middleware(['auth', \App\Http\Middleware\EnsureFirstTimeLoginComplete::class, 'module.context:trace'])
    ->prefix('trace')
    ->name('module.trace.')
    ->group(function () {
        Route::get('/', [ModuleDashboardController::class, 'index'])
             ->defaults('moduleCode', 'trace')
             ->name('dashboard');
             
        // Ruang untuk route TRACE lainnya
    });
