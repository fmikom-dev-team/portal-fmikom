<?php

use App\Modules\WorkOs\Controllers\OrganizationsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WorkOS Organizations (Modules) Management
|--------------------------------------------------------------------------
*/
Route::prefix('modules')->name('modules.')->group(function () {
    Route::post('/', [OrganizationsController::class, 'store'])->name('store');
    Route::patch('/{module}', [OrganizationsController::class, 'update'])->name('update');
    Route::delete('/{module}', [OrganizationsController::class, 'destroy'])->name('destroy');
    Route::post('/{module}/settings-data', [OrganizationsController::class, 'updateSettingsData'])->name('settings-data.update');

    Route::post('/{module}/roles', [OrganizationsController::class, 'addRole'])->name('roles.add');
    Route::delete('/{module}/roles/{role}', [OrganizationsController::class, 'removeRole'])->name('roles.remove');

    // Organization invitations routes
    Route::get('/{module}/invitations', [OrganizationsController::class, 'indexInvitations'])->name('invitations');
    Route::post('/{module}/invitations', [OrganizationsController::class, 'sendInvitation'])->name('invitations.send');
    Route::delete('/{module}/invitations/clear', [OrganizationsController::class, 'clearInvitations'])->name('invitations.clear');
    Route::delete('/{module}/invitations/{invitation}', [OrganizationsController::class, 'deleteInvitation'])->name('invitations.delete');
});
