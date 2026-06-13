<?php

use App\Http\Controllers\Wims\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Wims\Admin\CompanyController as AdminCompanyController;
use App\Http\Controllers\Wims\Admin\MonitoringController as AdminMonitoringController;
use App\Http\Controllers\Wims\Admin\AssessmentRecapController as AdminAssessmentRecapController;
use App\Http\Controllers\Wims\Admin\PlacementController as AdminPlacementController;
use App\Http\Controllers\Wims\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Wims\Admin\AssessmentTemplateController as AdminAssessmentTemplateController;
use App\Http\Controllers\Wims\Dosen\DashboardController as DosenDashboardController;
use App\Http\Controllers\Wims\Dosen\MonitoringController;
use App\Http\Controllers\Wims\Dosen\PenilaianMahasiswaController;
use App\Http\Controllers\Wims\Mahasiswa\AttendanceController;
use App\Http\Controllers\Wims\Mahasiswa\DashboardController;
use App\Http\Controllers\Wims\Mahasiswa\KetidakhadiranController as MahasiswaKetidakhadiranController;
use App\Http\Controllers\Wims\Mahasiswa\LaporanController;
use App\Http\Controllers\Wims\Mahasiswa\LogbookController;
use App\Http\Controllers\Wims\Mahasiswa\ProfileController as MahasiswaProfileController;
use App\Http\Controllers\Wims\Mahasiswa\RegistrationController as MahasiswaRegistrationController;
use App\Http\Controllers\Wims\Mitra\DashboardController as MitraDashboardController;
use App\Http\Controllers\Wims\Mitra\KetidakhadiranController as MitraKetidakhadiranController;
use App\Http\Controllers\Wims\Mitra\LogbookController as MitraLogbookController;
use App\Http\Controllers\Wims\Mitra\MonitoringController as MitraMonitoringController;
use App\Http\Controllers\Wims\Mitra\PenilaianMahasiswaController as MitraPenilaianMahasiswaController;
use Illuminate\Support\Facades\Route;

Route::prefix('wims')
    ->name('wims.')
    ->middleware(['auth', 'wims.access'])
    ->group(function () {
        Route::prefix('admin')
            ->name('admin.')
            ->middleware(['wims.role:super-admin'])
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

                Route::post('/penempatan/complete-selected', [AdminPlacementController::class, 'completeSelected'])
                    ->name('placements.complete-selected');

                Route::post('/penempatan/complete-filtered', [AdminPlacementController::class, 'completeFiltered'])
                    ->name('placements.complete-filtered');

                Route::put('/penempatan/{pendaftaran}', [AdminPlacementController::class, 'update'])
                    ->name('placements.update');

                Route::post('/penempatan/{pendaftaran}/generate-surat', [AdminPlacementController::class, 'generateSurat'])
                    ->name('placements.generate-surat');

                Route::post('/penempatan/{pendaftaran}/activate', [AdminPlacementController::class, 'activate'])
                    ->name('placements.activate');

                Route::post('/penempatan/{pendaftaran}/complete', [AdminPlacementController::class, 'complete'])
                    ->name('placements.complete');
            });

        Route::middleware(['wims.role:user,super-admin'])->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])
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

        Route::prefix('dosen')
            ->name('dosen.')
            ->middleware(['wims.role:dosen,super-admin'])
            ->group(function () {
                Route::get('/dashboard', [DosenDashboardController::class, 'index'])
                    ->name('dashboard');

                Route::get('/monitoring', [MonitoringController::class, 'index'])
                    ->name('monitoring.index');

                Route::get('/monitoring/{mahasiswa}', [MonitoringController::class, 'show'])
                    ->name('monitoring.show');

                Route::get('/penilaian-mahasiswa', [PenilaianMahasiswaController::class, 'index'])
                    ->name('assessments.index');

                Route::get('/penilaian-mahasiswa/{pendaftaran}', [PenilaianMahasiswaController::class, 'show'])
                    ->name('assessments.show');

                Route::post('/penilaian-mahasiswa/{pendaftaran}', [PenilaianMahasiswaController::class, 'store'])
                    ->name('assessments.store');

                Route::get('/penilaian-mahasiswa/{pendaftaran}/final-report/view', [PenilaianMahasiswaController::class, 'viewFinalReport'])
                    ->name('assessments.final-report.view');

                Route::get('/penilaian-mahasiswa/{pendaftaran}/final-report/download', [PenilaianMahasiswaController::class, 'downloadFinalReport'])
                    ->name('assessments.final-report.download');
            });

        Route::prefix('mitra')
            ->name('mitra.')
            ->middleware(['wims.role:mitra,super-admin'])
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
    });
