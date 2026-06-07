<?php

use Illuminate\Support\Facades\Route;
use App\Modules\WorkOs\Controllers\Auth\PasswordPolicyController;

/*
|--------------------------------------------------------------------------
| Password Policy Routes — Admin Only
|--------------------------------------------------------------------------
*/
Route::prefix('auth/password-policies')
    ->name('auth.password-policies.')
    ->middleware(['auth', 'role:super-admin'])
    ->group(function () {

        Route::get('/', [PasswordPolicyController::class, 'index'])->name('index');
        Route::patch('/global', [PasswordPolicyController::class, 'updateGlobal'])->name('global.update');
        Route::patch('/organization/{organization}', [PasswordPolicyController::class, 'updateForOrganization'])
            ->name('organization.update');
    });
