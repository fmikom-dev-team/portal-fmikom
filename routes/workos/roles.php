<?php

use App\Modules\WorkOs\Controllers\RolesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WorkOS Roles Management
|--------------------------------------------------------------------------
*/
Route::prefix('roles')->name('roles.')->group(function () {
    Route::post('/', [RolesController::class, 'store'])->name('store');
    Route::patch('/{role}', [RolesController::class, 'update'])->name('update');
    Route::delete('/{role}', [RolesController::class, 'destroy'])->name('destroy');
    Route::patch('/{role}/permissions', [RolesController::class, 'syncPermissions'])->name('permissions.sync');
});
