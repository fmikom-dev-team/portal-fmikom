<?php

use App\Modules\WorkOs\Controllers\RadarController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WorkOS Radar Security Module
|--------------------------------------------------------------------------
*/
Route::prefix('radar')->name('radar.')->group(function () {
    Route::patch('/config', [RadarController::class, 'updateConfig'])->name('config.update');
    Route::post('/blocked-items', [RadarController::class, 'storeBlockedItem'])->name('blocked-items.store');
    Route::delete('/blocked-items/{id}', [RadarController::class, 'destroyBlockedItem'])->name('blocked-items.destroy');
    Route::delete('/detections', [RadarController::class, 'resetDetections'])->name('detections.reset');
});
