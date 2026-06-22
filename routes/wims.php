<?php

use App\Http\Middleware\EnsureFirstTimeLoginComplete;
use App\Modules\Wims\Controllers\Admin\AssessmentRecapController as AdminAssessmentRecapController;
use App\Modules\Wims\Controllers\Admin\AssessmentTemplateController as AdminAssessmentTemplateController;
use App\Modules\Wims\Controllers\Admin\CompanyController as AdminCompanyController;
use App\Modules\Wims\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Modules\Wims\Controllers\Admin\MonitoringController as AdminMonitoringController;
use App\Modules\Wims\Controllers\Admin\PlacementController as AdminPlacementController;
use App\Modules\Wims\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Modules\Wims\Controllers\Dosen\DashboardController as DosenDashboardController;
use App\Modules\Wims\Controllers\Dosen\MonitoringController as DosenMonitoringController;
use App\Modules\Wims\Controllers\Dosen\PenilaianMahasiswaController as DosenPenilaianMahasiswaController;
use App\Modules\Wims\Controllers\Mahasiswa\AttendanceController;
use App\Modules\Wims\Controllers\Mahasiswa\DashboardController as MahasiswaDashboardController;
use App\Modules\Wims\Controllers\Mahasiswa\KetidakhadiranController as MahasiswaKetidakhadiranController;
use App\Modules\Wims\Controllers\Mahasiswa\LaporanController;
use App\Modules\Wims\Controllers\Mahasiswa\LogbookController;
use App\Modules\Wims\Controllers\Mahasiswa\ProfileController as MahasiswaProfileController;
use App\Modules\Wims\Controllers\Mahasiswa\RegistrationController as MahasiswaRegistrationController;
use App\Modules\Wims\Controllers\Mitra\DashboardController as MitraDashboardController;
use App\Modules\Wims\Controllers\Mitra\KetidakhadiranController as MitraKetidakhadiranController;
use App\Modules\Wims\Controllers\Mitra\LogbookController as MitraLogbookController;
use App\Modules\Wims\Controllers\Mitra\MonitoringController as MitraMonitoringController;
use App\Modules\Wims\Controllers\Mitra\PenilaianMahasiswaController as MitraPenilaianMahasiswaController;
use App\Modules\Wims\Controllers\WimsDashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:wims'])
    ->prefix('wims')
    ->group(function () {
        Route::get('/', [WimsDashboardController::class, 'index'])
            ->name('module.wims.dashboard');
    });

Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:wims,mahasiswa'])
    ->prefix('wims')
    ->name('wims.')
    ->group(function () {
        Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/profil', [MahasiswaProfileController::class, 'index'])
            ->name('profile');
        Route::post('/profil', [MahasiswaProfileController::class, 'update'])
            ->name('profile.update');

        Route::get('/pendaftaran', [MahasiswaRegistrationController::class, 'index'])
            ->name('registration');
        Route::post('/pendaftaran', [MahasiswaRegistrationController::class, 'store'])
            ->name('registration.store');

        Route::get('/absensi', [AttendanceController::class, 'index'])
            ->name('attendance');
        Route::post('/absensi', [AttendanceController::class, 'store'])
            ->name('absensi.store');
        Route::post('/absensi/checkout', [AttendanceController::class, 'checkout'])
            ->name('absensi.checkout');
        Route::get('/absensi/download', [AttendanceController::class, 'downloadHistory'])
            ->name('absensi.download');

        Route::post('/ketidakhadiran', [MahasiswaKetidakhadiranController::class, 'store'])
            ->name('absence.store');
        Route::delete('/ketidakhadiran/{ketidakhadiran}', [MahasiswaKetidakhadiranController::class, 'destroy'])
            ->name('absence.destroy');

        Route::get('/logbook', [LogbookController::class, 'index'])
            ->name('logbook');
        Route::post('/logbook', [LogbookController::class, 'store'])
            ->name('logbook.store');
        Route::put('/logbook/{logbook}', [LogbookController::class, 'update'])
            ->name('logbook.update');
        Route::get('/logbook/download', [LogbookController::class, 'downloadCurrentPeriod'])
            ->name('logbook.download');

        Route::get('/laporan', [LaporanController::class, 'index'])
            ->name('laporan');
        Route::post('/laporan', [LaporanController::class, 'store'])
            ->name('laporan.store');
        Route::get('/laporan/final-report/view', [LaporanController::class, 'viewFinalReport'])
            ->name('laporan.final-report.view');
        Route::get('/laporan/final-report/download', [LaporanController::class, 'downloadFinalReport'])
            ->name('laporan.final-report.download');
    });

Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:wims,super-admin,admin,admin-universitas,admin-akademik,prodi'])
    ->prefix('wims/admin')
    ->name('wims.admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/perusahaan', [AdminCompanyController::class, 'index'])
            ->name('companies.index');
        Route::post('/perusahaan', [AdminCompanyController::class, 'store'])
            ->name('companies.store');
        Route::put('/perusahaan/{company}', [AdminCompanyController::class, 'update'])
            ->name('companies.update');
        Route::delete('/perusahaan/{company}', [AdminCompanyController::class, 'destroy'])
            ->name('companies.destroy');
        Route::post('/perusahaan/{company}/account', [AdminCompanyController::class, 'storeAccount'])
            ->name('companies.account.store');

        Route::get('/pendaftaran', [AdminRegistrationController::class, 'index'])
            ->name('registrations.index');
        Route::patch('/pendaftaran/{pendaftaran}/status', [AdminRegistrationController::class, 'updateStatus'])
            ->name('registrations.update-status');

        Route::get('/penempatan', [AdminPlacementController::class, 'index'])
            ->name('placements.index');
        Route::post('/penempatan/complete-selected', [AdminPlacementController::class, 'completeSelected'])
            ->name('placements.complete-selected');
        Route::post('/penempatan/complete-filtered', [AdminPlacementController::class, 'completeFiltered'])
            ->name('placements.complete-filtered');
        Route::put('/penempatan/{pendaftaran}', [AdminPlacementController::class, 'update'])
            ->name('placements.update');
        Route::post('/penempatan/{pendaftaran}/activate', [AdminPlacementController::class, 'activate'])
            ->name('placements.activate');
        Route::post('/penempatan/{pendaftaran}/complete', [AdminPlacementController::class, 'complete'])
            ->name('placements.complete');

        Route::get('/monitoring', [AdminMonitoringController::class, 'index'])
            ->name('monitoring.index');

        Route::get('/rekap-nilai', [AdminAssessmentRecapController::class, 'index'])
            ->name('assessment-recap.index');
        Route::get('/rekap-nilai/{pendaftaran}/download/{role}', [AdminAssessmentRecapController::class, 'download'])
            ->whereIn('role', ['dosen', 'mitra'])
            ->name('assessment-recap.download');

        Route::get('/penilaian-template', [AdminAssessmentTemplateController::class, 'index'])
            ->name('assessment-templates.index');
        Route::post('/penilaian-template', [AdminAssessmentTemplateController::class, 'store'])
            ->name('assessment-templates.store');
        Route::put('/penilaian-template/{assessmentTemplate}', [AdminAssessmentTemplateController::class, 'update'])
            ->name('assessment-templates.update');
        Route::delete('/penilaian-template/{assessmentTemplate}', [AdminAssessmentTemplateController::class, 'destroy'])
            ->name('assessment-templates.destroy');
    });

Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:wims,dosen'])
    ->prefix('wims/dosen')
    ->name('wims.dosen.')
    ->group(function () {
        Route::get('/dashboard', [DosenDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/monitoring', [DosenMonitoringController::class, 'index'])
            ->name('monitoring.index');
        Route::get('/monitoring/{mahasiswa}', [DosenMonitoringController::class, 'show'])
            ->name('monitoring.show');

        Route::get('/penilaian-mahasiswa', [DosenPenilaianMahasiswaController::class, 'index'])
            ->name('assessments.index');
        Route::get('/penilaian-mahasiswa/{pendaftaran}', [DosenPenilaianMahasiswaController::class, 'show'])
            ->name('assessments.show');
        Route::post('/penilaian-mahasiswa/{pendaftaran}', [DosenPenilaianMahasiswaController::class, 'store'])
            ->name('assessments.store');
        Route::get('/penilaian-mahasiswa/{pendaftaran}/final-report/view', [DosenPenilaianMahasiswaController::class, 'viewFinalReport'])
            ->name('assessments.final-report.view');
        Route::get('/penilaian-mahasiswa/{pendaftaran}/final-report/download', [DosenPenilaianMahasiswaController::class, 'downloadFinalReport'])
            ->name('assessments.final-report.download');
    });

Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:wims,mitra'])
    ->prefix('wims/mitra')
    ->name('wims.mitra.')
    ->group(function () {
        Route::get('/dashboard', [MitraDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/monitoring', [MitraMonitoringController::class, 'index'])
            ->name('monitoring.index');
        Route::get('/monitoring/{mahasiswa}', [MitraMonitoringController::class, 'show'])
            ->name('monitoring.show');

        Route::get('/penilaian-mahasiswa', [MitraPenilaianMahasiswaController::class, 'index'])
            ->name('assessments.index');
        Route::get('/penilaian-mahasiswa/{pendaftaran}', [MitraPenilaianMahasiswaController::class, 'show'])
            ->name('assessments.show');
        Route::post('/penilaian-mahasiswa/{pendaftaran}', [MitraPenilaianMahasiswaController::class, 'store'])
            ->name('assessments.store');
        Route::get('/penilaian-mahasiswa/{pendaftaran}/final-report/view', [MitraPenilaianMahasiswaController::class, 'viewFinalReport'])
            ->name('assessments.final-report.view');
        Route::get('/penilaian-mahasiswa/{pendaftaran}/final-report/download', [MitraPenilaianMahasiswaController::class, 'downloadFinalReport'])
            ->name('assessments.final-report.download');

        Route::post('/ketidakhadiran/{ketidakhadiran}/approve', [MitraKetidakhadiranController::class, 'approve'])
            ->name('absence.approve');
        Route::post('/ketidakhadiran/{ketidakhadiran}/reject', [MitraKetidakhadiranController::class, 'reject'])
            ->name('absence.reject');

        Route::post('/logbook/{logbook}/review', [MitraLogbookController::class, 'review'])
            ->name('logbook.review');
    });
