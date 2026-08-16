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
    Route::patch('/security/{id}/status', [AuditLogsController::class, 'updateIncidentStatus'])->name('security.status');
    Route::delete('/security/{id}', [AuditLogsController::class, 'destroyIncident'])->name('security.destroy');
    Route::post('/security/clear', [AuditLogsController::class, 'clearSecurityIncidents'])->name('security.clear');
    Route::post('/clear', [AuditLogsController::class, 'clear'])->name('clear');
});
