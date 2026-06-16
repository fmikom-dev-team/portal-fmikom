<?php

use App\Http\Middleware\EnsureFirstTimeLoginComplete;
use App\Modules\Trace\Controllers\TraceDashboardController;
use App\Modules\Trace\Controllers\NotificationController;
use App\Modules\Trace\Controllers\Alumni\TraceAlumniProfileController;
use App\Modules\Trace\Controllers\Alumni\CareerController;
use App\Modules\Trace\Controllers\Alumni\AlumniKuesionerController;
use App\Modules\Trace\Controllers\Alumni\JobBrowseController;
use App\Modules\Trace\Controllers\Alumni\EventController as AlumniEventController;
use App\Modules\Trace\Controllers\Admin\DashboardAdminController;
use App\Modules\Trace\Controllers\Admin\KuesionerController;
use App\Modules\Trace\Controllers\Admin\KuesionerAnalyticsController;
use App\Modules\Trace\Controllers\Admin\MapController;
use App\Modules\Trace\Controllers\Admin\AnalyticsController;
use App\Modules\Trace\Controllers\Admin\StatisticsController;
use App\Modules\Trace\Controllers\Admin\RespondenController;
use App\Modules\Trace\Controllers\Admin\JobManagementController;
use App\Modules\Trace\Controllers\Admin\EventController as AdminEventController;
use App\Modules\Trace\Controllers\Admin\ActivityLogController;
use App\Modules\Trace\Controllers\Mitra\DashboardMitraController;
use App\Modules\Trace\Controllers\Mitra\MitraProfileController;
use App\Modules\Trace\Controllers\Mitra\JobListingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Trace Module Routes
|--------------------------------------------------------------------------
|
| Base middleware: auth, first-login check, module context
| Role-based: alumni (throttle:30/min), admin (throttle:60/min), mitra (throttle:30/min)
|
*/

$baseMiddleware = ['auth', EnsureFirstTimeLoginComplete::class, 'module.context:trace'];

// ─────────────────────────────────────────────────────────────────────────────
// Shared Routes (all authenticated users)
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware($baseMiddleware)
    ->prefix('trace')
    ->name('module.trace.')
    ->group(function () {
        // Dashboard — routes user based on their role
        Route::get('/', [TraceDashboardController::class, 'index'])->name('dashboard');

        // Notifications (shared across all roles)
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
        Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markRead'])->name('notifications.mark-read');
    });

// ─────────────────────────────────────────────────────────────────────────────
// Alumni Routes
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware([...$baseMiddleware, 'role:alumni', 'throttle:30,1'])
    ->prefix('trace')
    ->name('module.trace.')
    ->group(function () {
        // Profile
        Route::get('/profile-alumni', [TraceAlumniProfileController::class, 'index'])->name('profile-alumni');
        Route::post('/profile-alumni', [TraceAlumniProfileController::class, 'update'])->name('profile-alumni.update');

        // Career
        Route::get('/career', [CareerController::class, 'index'])->name('career');
        Route::post('/career', [CareerController::class, 'store'])->name('career.store');
        Route::put('/career/{id}', [CareerController::class, 'update'])->name('career.update');
        Route::delete('/career/{id}', [CareerController::class, 'destroy'])->name('career.destroy');
        Route::post('/career/{id}/set-current', [CareerController::class, 'setCurrent'])->name('career.set-current');

        // Kuesioner
        Route::get('/kuesioner', [AlumniKuesionerController::class, 'index'])->name('kuesioner');
        Route::get('/kuesioner/{id}', [AlumniKuesionerController::class, 'show'])->name('kuesioner.show');
        Route::post('/kuesioner/{id}', [AlumniKuesionerController::class, 'store'])->name('kuesioner.store');
    });

// Alumni — Jobs
Route::middleware([...$baseMiddleware, 'role:alumni', 'throttle:30,1'])
    ->prefix('trace/jobs')
    ->name('module.trace.jobs.')
    ->group(function () {
        Route::get('/', [JobBrowseController::class, 'index'])->name('index');
        Route::get('/my-applications', [JobBrowseController::class, 'myApplications'])->name('my-applications');
        Route::get('/my-bookmarks', [JobBrowseController::class, 'myBookmarks'])->name('my-bookmarks');
        Route::get('/companies', [JobBrowseController::class, 'companies'])->name('companies');
        Route::get('/{id}', [JobBrowseController::class, 'show'])->name('show');
        Route::post('/{id}/apply', [JobBrowseController::class, 'apply'])->name('apply');
        Route::post('/{id}/bookmark', [JobBrowseController::class, 'toggleBookmark'])->name('bookmark');
    });

// Alumni — Events
Route::middleware([...$baseMiddleware, 'role:alumni', 'throttle:30,1'])
    ->prefix('trace/events')
    ->name('module.trace.events.')
    ->group(function () {
        Route::get('/', [AlumniEventController::class, 'index'])->name('index');
        Route::get('/my-events', [AlumniEventController::class, 'myEvents'])->name('my-events');
        Route::get('/{id}', [AlumniEventController::class, 'show'])->name('show');
        Route::post('/{id}/register', [AlumniEventController::class, 'register'])->name('register');
        Route::post('/{id}/cancel', [AlumniEventController::class, 'cancelRegistration'])->name('cancel');
    });

// ─────────────────────────────────────────────────────────────────────────────
// Admin Routes
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware([...$baseMiddleware, 'role:admin,super-admin', 'throttle:60,1'])
    ->prefix('trace/admin')
    ->name('module.trace.')
    ->group(function () {
        Route::get('/', [DashboardAdminController::class, 'index'])->name('admin.dashboard');

        // Map & Analytics
        Route::get('/map', [MapController::class, 'index'])->name('map');
        Route::get('/map/data', [MapController::class, 'getData'])->name('map.data');
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
        Route::get('/laporan', [StatisticsController::class, 'index'])->name('laporan');

        // Job Management
        Route::get('/jobs', [JobManagementController::class, 'index'])->name('admin.jobs');
        Route::get('/jobs/create', [JobManagementController::class, 'create'])->name('admin.jobs.create');
        Route::post('/jobs', [JobManagementController::class, 'store'])->name('admin.jobs.store');
        Route::post('/jobs/bulk-approve', [JobManagementController::class, 'bulkApprove'])->name('admin.jobs.bulk-approve');
        Route::post('/jobs/bulk-reject', [JobManagementController::class, 'bulkReject'])->name('admin.jobs.bulk-reject');
        Route::get('/jobs/{id}/edit', [JobManagementController::class, 'edit'])->name('admin.jobs.edit');
        Route::get('/jobs/{id}', [JobManagementController::class, 'show'])->name('admin.jobs.show');
        Route::put('/jobs/{id}', [JobManagementController::class, 'update'])->name('admin.jobs.update');
        Route::put('/jobs/{id}/approve', [JobManagementController::class, 'approve'])->name('admin.jobs.approve');
        Route::put('/jobs/{id}/reject', [JobManagementController::class, 'reject'])->name('admin.jobs.reject');
        Route::delete('/jobs/{id}', [JobManagementController::class, 'destroy'])->name('admin.jobs.destroy');
        Route::put('/jobs/{jobId}/applicants/{applicantId}/status', [JobManagementController::class, 'updateApplicantStatus'])->name('admin.applicant-status');

        // Activity Log
        Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('admin.activity-log');
    });

// Admin — Kuesioner
Route::middleware([...$baseMiddleware, 'role:admin,super-admin', 'throttle:60,1'])
    ->prefix('trace/admin/questionnaires')
    ->name('module.trace.admin.questionnaires.')
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

// Admin — Alumni Management
Route::middleware([...$baseMiddleware, 'role:admin,super-admin', 'throttle:60,1'])
    ->prefix('trace/admin/alumni')
    ->name('module.trace.admin.alumni.')
    ->group(function () {
        Route::get('/', [RespondenController::class, 'index'])->name('index');
        Route::get('/{id}', [RespondenController::class, 'show'])->name('show');
    });

// Admin — Events
Route::middleware([...$baseMiddleware, 'role:admin,super-admin', 'throttle:60,1'])
    ->prefix('trace/admin/events')
    ->name('module.trace.admin.events.')
    ->group(function () {
        Route::get('/', [AdminEventController::class, 'index'])->name('index');
        Route::get('/create', [AdminEventController::class, 'create'])->name('create');
        Route::post('/', [AdminEventController::class, 'store'])->name('store');
        Route::get('/{id}', [AdminEventController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [AdminEventController::class, 'edit'])->name('edit');
        Route::put('/{id}', [AdminEventController::class, 'update'])->name('update');
        Route::put('/{id}/status', [AdminEventController::class, 'updateStatus'])->name('update-status');
        Route::delete('/{id}', [AdminEventController::class, 'destroy'])->name('destroy');
        Route::post('/{eventId}/registrations/{registrationId}/attendance', [AdminEventController::class, 'markAttendance'])->name('mark-attendance');
    });

// ─────────────────────────────────────────────────────────────────────────────
// Mitra Routes
// ─────────────────────────────────────────────────────────────────────────────
Route::middleware([...$baseMiddleware, 'role:admin,super-admin,mitra', 'throttle:30,1'])
    ->prefix('trace/open-job')
    ->name('module.trace.open-job.')
    ->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardMitraController::class, 'index'])->name('mitra-dashboard');

        // Profile
        Route::get('/profile-setup', [MitraProfileController::class, 'setup'])->name('mitra-profile-setup');
        Route::post('/profile-setup', [MitraProfileController::class, 'store'])->name('mitra-profile-setup.store');
        Route::get('/profile', [MitraProfileController::class, 'edit'])->name('mitra-profile');
        Route::put('/profile', [MitraProfileController::class, 'update'])->name('mitra-profile.update');

        // Job Listings
        Route::get('/jobs-listings', [JobListingController::class, 'index'])->name('jobs-listings');
        Route::get('/jobs-listings/create', [JobListingController::class, 'create'])->name('mitra.jobs-listings.create');
        Route::post('/jobs-listings', [JobListingController::class, 'store'])->name('mitra.jobs-listings.store');
        Route::get('/jobs-listings/{id}', [JobListingController::class, 'show'])->name('detail-job');
        Route::put('/jobs-listings/{id}', [JobListingController::class, 'update'])->name('mitra.jobs-listings.update');
        Route::delete('/jobs-listings/{id}', [JobListingController::class, 'destroy'])->name('mitra.jobs-listings.destroy');
        Route::put('/jobs-listings/{jobId}/applicants/{applicantId}/status', [JobListingController::class, 'updateApplicantStatus'])->name('mitra.applicant-status');
        Route::put('/jobs-listings/{id}/submit-review', [JobListingController::class, 'submitForReview'])->name('mitra.jobs-listings.submit-review');
    });