<?php

use App\Modules\WorkOs\Controllers\PipesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WorkOS Pipes — Data Integration Platform
|--------------------------------------------------------------------------
*/
Route::prefix('pipes')->name('pipes.')->group(function () {
    Route::get('/dashboard', [PipesController::class, 'dashboard'])->name('dashboard');
    Route::get('/providers', [PipesController::class, 'providers'])->name('providers');
    Route::get('/providers/{provider}', [PipesController::class, 'showProvider'])->name('providers.show');
    Route::patch('/providers/{provider:slug}', [PipesController::class, 'updateProvider'])->name('providers.update');
    Route::get('/connections', [PipesController::class, 'connections'])->name('connections');
    Route::post('/connections/{connection}/sync', [PipesController::class, 'syncConnection'])->name('connections.sync');
    Route::get('/workflows', [PipesController::class, 'workflows'])->name('workflows');
    Route::post('/connect/{provider}', [PipesController::class, 'connectProvider'])->name('oauth.connect');

    // Pipes OAuth callback is also public — outside auth middleware
    // Registered separately in bootstrap/app.php via pipes-oauth route file
});
