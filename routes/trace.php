<?php

use App\Http\Middleware\EnsureFirstTimeLoginComplete;
use App\Modules\Trace\Controllers\TraceDashboardController;
use App\Modules\Trace\Controllers\Alumni\TraceAlumniProfileController;
use App\Modules\Trace\Controllers\Alumni\CareerController;
use App\Modules\Trace\Controllers\Alumni\TraceAlumniDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:trace'])
    ->prefix('trace')
    ->name('module.trace.')
    ->group(function () {
        Route::get('/', [TraceDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile-alumni', [TraceAlumniProfileController::class, 'index'])->name('profile-alumni');
        Route::post('/profile-alumni', [TraceAlumniProfileController::class, 'update'])->name('profile-alumni.update');

        Route::get('/career', [CareerController::class, 'index'])->name('career');
        Route::post('/career', [CareerController::class, 'store'])->name('career.store');
        Route::put('/career/{id}', [CareerController::class, 'update'])->name('career.update');
        Route::delete('/career/{id}', [CareerController::class, 'destroy'])->name('career.destroy');
        Route::post('/career/{id}/set-current', [CareerController::class, 'setCurrent'])->name('career.set-current');
    });