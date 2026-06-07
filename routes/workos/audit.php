<?php

use App\Modules\WorkOs\Controllers\AuditLogsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WorkOS Audit Logs
|--------------------------------------------------------------------------
*/
Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
    Route::get('/events', [AuditLogsController::class, 'events'])->name('events');
    Route::get('/security', [AuditLogsController::class, 'securityLogs'])->name('security');
    Route::post('/clear', [AuditLogsController::class, 'clear'])->name('clear');
});
