<?php

use App\Http\Middleware\EnsureFirstTimeLoginComplete;
use App\Modules\Trace\Controllers\TraceDashboardController;
use App\Modules\Trace\Controllers\Alumni\TraceAlumniProfileController;
use App\Modules\Trace\Controllers\Alumni\CareerController;
use App\Modules\Trace\Controllers\Alumni\TraceAlumniDashboardController;
use App\Modules\Trace\Controllers\Alumni\AlumniKuesionerController;
use App\Modules\Trace\Controllers\Admin\DashboardAdminController;
use App\Modules\Trace\Controllers\Admin\KuesionerController;
use App\Modules\Trace\Controllers\Admin\KuesionerAnalyticsController;
use App\Modules\Trace\Controllers\Admin\MapController;
use App\Modules\Trace\Controllers\Admin\AnalyticsController;
use App\Modules\Trace\Controllers\Admin\StatisticsController;
use App\Modules\Trace\Controllers\Admin\RespondenController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:trace'])
    ->prefix('trace')
    ->name('module.trace.')
    ->group(function () {
        Route::get('/', [TraceDashboardController::class, 'index'])->name('dashboard');

        // Notification routes (shared across all roles)
        Route::post('/notifications/mark-all-read', [\App\Modules\Trace\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
        Route::post('/notifications/{id}/mark-read', [\App\Modules\Trace\Controllers\NotificationController::class, 'markRead'])->name('notifications.mark-read');

        Route::middleware('role:alumni')->group(function () {
            Route::get('/profile-alumni', [TraceAlumniProfileController::class, 'index'])->name('profile-alumni');
            Route::post('/profile-alumni', [TraceAlumniProfileController::class, 'update'])->name('profile-alumni.update');

            Route::get('/career', [CareerController::class, 'index'])->name('career');
            Route::post('/career', [CareerController::class, 'store'])->name('career.store');
            Route::put('/career/{id}', [CareerController::class, 'update'])->name('career.update');
            Route::delete('/career/{id}', [CareerController::class, 'destroy'])->name('career.destroy');
            Route::post('/career/{id}/set-current', [CareerController::class, 'setCurrent'])->name('career.set-current');

            Route::get('/kuesioner', [AlumniKuesionerController::class, 'index'])->name('kuesioner');
            Route::get('/kuesioner/{id}', [AlumniKuesionerController::class, 'show'])->name('kuesioner.show');
            Route::post('/kuesioner/{id}', [AlumniKuesionerController::class, 'store'])->name('kuesioner.store');
        });
    });

Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:trace', 'role:admin,super-admin'])
    ->prefix('trace/admin')
    ->name('module.trace.')
    ->group(function () {
        Route::get('/', [DashboardAdminController::class, 'index'])->name('admin.dashboard');

        Route::get('/map', [MapController::class, 'index'])->name('map');
        Route::get('/map/data', [MapController::class, 'getData'])->name('map.data');

        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
        Route::get('/laporan', [StatisticsController::class, 'index'])->name('laporan');

        // Admin Job Management
        Route::get('/jobs', [\App\Modules\Trace\Controllers\Admin\JobManagementController::class, 'index'])->name('admin.jobs');
        Route::get('/jobs/create', [\App\Modules\Trace\Controllers\Admin\JobManagementController::class, 'create'])->name('admin.jobs.create');
        Route::post('/jobs', [\App\Modules\Trace\Controllers\Admin\JobManagementController::class, 'store'])->name('admin.jobs.store');
        Route::post('/jobs/bulk-approve', [\App\Modules\Trace\Controllers\Admin\JobManagementController::class, 'bulkApprove'])->name('admin.jobs.bulk-approve');
        Route::post('/jobs/bulk-reject', [\App\Modules\Trace\Controllers\Admin\JobManagementController::class, 'bulkReject'])->name('admin.jobs.bulk-reject');
        Route::get('/jobs/{id}/edit', [\App\Modules\Trace\Controllers\Admin\JobManagementController::class, 'edit'])->name('admin.jobs.edit');
        Route::get('/jobs/{id}', [\App\Modules\Trace\Controllers\Admin\JobManagementController::class, 'show'])->name('admin.jobs.show');
        Route::put('/jobs/{id}', [\App\Modules\Trace\Controllers\Admin\JobManagementController::class, 'update'])->name('admin.jobs.update');
        Route::put('/jobs/{id}/approve', [\App\Modules\Trace\Controllers\Admin\JobManagementController::class, 'approve'])->name('admin.jobs.approve');
        Route::put('/jobs/{id}/reject', [\App\Modules\Trace\Controllers\Admin\JobManagementController::class, 'reject'])->name('admin.jobs.reject');
        Route::delete('/jobs/{id}', [\App\Modules\Trace\Controllers\Admin\JobManagementController::class, 'destroy'])->name('admin.jobs.destroy');
        Route::put('/jobs/{jobId}/applicants/{applicantId}/status', [\App\Modules\Trace\Controllers\Admin\JobManagementController::class, 'updateApplicantStatus'])->name('admin.applicant-status');

        Route::get('/activity-log', [\App\Modules\Trace\Controllers\Admin\ActivityLogController::class, 'index'])->name('admin.activity-log');
    });

Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:trace', 'role:admin,super-admin'])
    ->prefix('admin/quesionnaires')
    ->name('quesionnaires.')
    ->group(function () {
        Route::get('/', [KuesionerController::class, 'index'])->name('index');
        Route::get('/create', [KuesionerController::class, 'create'])->name('create');
        Route::post('/', [KuesionerController::class, 'store'])->name('store');
        Route::get('/{id}', [KuesionerController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [KuesionerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [KuesionerController::class, 'update'])->name('update');
        Route::delete('/{id}', [KuesionerController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/duplicate', [KuesionerController::class, 'duplicate'])->name('duplicate');

        Route::get('/{id}/analytics-page', [KuesionerController::class, 'analyticsPage'])->name('analytics-page');
        Route::get('/{id}/analytics', [KuesionerAnalyticsController::class, 'getAnalytics'])->name('analytics');
        Route::get('/{id}/live-stats', [KuesionerAnalyticsController::class, 'liveStats'])->name('live-stats');
        Route::get('/{id}/respondents', [KuesionerAnalyticsController::class, 'getRespondents'])->name('respondents');
        Route::get('/{id}/export', [KuesionerAnalyticsController::class, 'export'])->name('export');
    });

Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:trace', 'role:admin,super-admin'])
    ->prefix('admin/alumni')
    ->name('alumni.')
    ->group(function () {
        Route::get('/', [RespondenController::class, 'index'])->name('index');
        Route::get('/{id}', [RespondenController::class, 'show'])->name('show');
    });

// Alumni Jobs Routes
Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:trace', 'role:alumni'])
    ->prefix('trace/jobs')
    ->name('module.trace.jobs.')
    ->group(function () {
        Route::get('/', [\App\Modules\Trace\Controllers\Alumni\JobBrowseController::class, 'index'])->name('index');
        Route::get('/my-applications', [\App\Modules\Trace\Controllers\Alumni\JobBrowseController::class, 'myApplications'])->name('my-applications');
        Route::get('/my-bookmarks', [\App\Modules\Trace\Controllers\Alumni\JobBrowseController::class, 'myBookmarks'])->name('my-bookmarks');
        Route::get('/companies', [\App\Modules\Trace\Controllers\Alumni\JobBrowseController::class, 'companies'])->name('companies');
        Route::get('/{id}', [\App\Modules\Trace\Controllers\Alumni\JobBrowseController::class, 'show'])->name('show');
        Route::post('/{id}/apply', [\App\Modules\Trace\Controllers\Alumni\JobBrowseController::class, 'apply'])->name('apply');
        Route::post('/{id}/bookmark', [\App\Modules\Trace\Controllers\Alumni\JobBrowseController::class, 'toggleBookmark'])->name('bookmark');
    });

// Admin Events Routes
Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:trace', 'role:admin,super-admin'])
    ->prefix('admin/events')
    ->name('events.')
    ->group(function () {
        Route::get('/', [\App\Modules\Trace\Controllers\Admin\EventController::class, 'index'])->name('index');
        Route::get('/create', [\App\Modules\Trace\Controllers\Admin\EventController::class, 'create'])->name('create');
        Route::post('/', [\App\Modules\Trace\Controllers\Admin\EventController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Modules\Trace\Controllers\Admin\EventController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [\App\Modules\Trace\Controllers\Admin\EventController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Modules\Trace\Controllers\Admin\EventController::class, 'update'])->name('update');
        Route::put('/{id}/status', [\App\Modules\Trace\Controllers\Admin\EventController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{id}', [\App\Modules\Trace\Controllers\Admin\EventController::class, 'destroy'])->name('destroy');
        Route::post('/{eventId}/registrations/{registrationId}/attendance', [\App\Modules\Trace\Controllers\Admin\EventController::class, 'markAttendance'])->name('mark-attendance');
    });

// Alumni Events Routes
Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:trace', 'role:alumni'])
    ->prefix('trace/events')
    ->name('module.trace.events.')
    ->group(function () {
        Route::get('/', [\App\Modules\Trace\Controllers\Alumni\EventController::class, 'index'])->name('index');
        Route::get('/my-events', [\App\Modules\Trace\Controllers\Alumni\EventController::class, 'myEvents'])->name('my-events');
        Route::get('/{id}', [\App\Modules\Trace\Controllers\Alumni\EventController::class, 'show'])->name('show');
        Route::post('/{id}/register', [\App\Modules\Trace\Controllers\Alumni\EventController::class, 'register'])->name('register');
        Route::post('/{id}/cancel', [\App\Modules\Trace\Controllers\Alumni\EventController::class, 'cancelRegistration'])->name('cancel');
    });

// Mitra Routes
Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:trace', 'role:admin,super-admin,mitra'])
    ->prefix('trace/open-job')
    ->name('module.trace.open-job.')
    ->group(function () {
        Route::get('/dashboard', [\App\Modules\Trace\Controllers\Mitra\DashboardMitraController::class, 'index'])->name('mitra-dashboard');

        Route::get('/profile-setup', [\App\Modules\Trace\Controllers\Mitra\MitraProfileController::class, 'setup'])->name('mitra-profile-setup');
        Route::post('/profile-setup', [\App\Modules\Trace\Controllers\Mitra\MitraProfileController::class, 'store'])->name('mitra-profile-setup.store');
        Route::get('/profile', [\App\Modules\Trace\Controllers\Mitra\MitraProfileController::class, 'edit'])->name('mitra-profile');
        Route::put('/profile', [\App\Modules\Trace\Controllers\Mitra\MitraProfileController::class, 'update'])->name('mitra-profile.update');

        Route::get('/jobs-listings', [\App\Modules\Trace\Controllers\Mitra\JobListingController::class, 'index'])->name('jobs-listings');
        Route::get('/jobs-listings/create', [\App\Modules\Trace\Controllers\Mitra\JobListingController::class, 'create'])->name('mitra.jobs-listings.create');
        Route::post('/jobs-listings', [\App\Modules\Trace\Controllers\Mitra\JobListingController::class, 'store'])->name('mitra.jobs-listings.store');
        Route::get('/jobs-listings/{id}', [\App\Modules\Trace\Controllers\Mitra\JobListingController::class, 'show'])->name('detail-job');
        Route::put('/jobs-listings/{id}', [\App\Modules\Trace\Controllers\Mitra\JobListingController::class, 'update'])->name('mitra.jobs-listings.update');
        Route::delete('/jobs-listings/{id}', [\App\Modules\Trace\Controllers\Mitra\JobListingController::class, 'destroy'])->name('mitra.jobs-listings.destroy');
        Route::put('/jobs-listings/{jobId}/applicants/{applicantId}/status', [\App\Modules\Trace\Controllers\Mitra\JobListingController::class, 'updateApplicantStatus'])->name('mitra.applicant-status');
        Route::put('/jobs-listings/{id}/submit-review', [\App\Modules\Trace\Controllers\Mitra\JobListingController::class, 'submitForReview'])->name('mitra.jobs-listings.submit-review');
    });