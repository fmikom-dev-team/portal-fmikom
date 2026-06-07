<?php

use App\Modules\WorkOs\Controllers\PermissionsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WorkOS Permissions Management
|--------------------------------------------------------------------------
*/
Route::prefix('permissions')->name('permissions.')->group(function () {
    Route::post('/', [PermissionsController::class, 'store'])->name('store');
    Route::patch('/{permission}', [PermissionsController::class, 'update'])->name('update');
    Route::delete('/{permission}', [PermissionsController::class, 'destroy'])->name('destroy');
});
