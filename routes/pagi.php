<?php

use App\Http\Middleware\EnsureFirstTimeLoginComplete;
use App\Modules\Pagi\Controllers\AdminDashboardController;
use App\Modules\Pagi\Controllers\AdminModerationController;
use App\Modules\Pagi\Controllers\AdminUserController;
use App\Modules\Pagi\Controllers\AdminWorkController;
use App\Modules\Pagi\Controllers\PagiChatController;
use App\Modules\Pagi\Controllers\PagiCvController;
use App\Modules\Pagi\Controllers\PagiDashboardController;
use App\Modules\Pagi\Controllers\PagiEditorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:pagi'])
    ->prefix('pagi')
    ->name('module.pagi.')
    ->group(function () {
        Route::get('/', [PagiDashboardController::class, 'index'])
            ->name('dashboard');
        Route::get('/instant-search', [PagiDashboardController::class, 'instantSearch'])
            ->name('instant-search');

        // Halaman People — dapat diakses semua role user di modul PAGI
        Route::get('/people', [PagiDashboardController::class, 'explorePeople'])
            ->name('people');

        // Halaman Gallery — menampung seluruh karya & galeri mahasiswa
        Route::get('/gallery', [PagiDashboardController::class, 'exploreGallery'])
            ->name('gallery');

        // Halaman Works — Pemilihan Template (Temporarily Disabled)
        Route::get('/works', function () {
            return redirect()->route('module.pagi.dashboard')->with('error', 'Fitur Works sementara dinonaktifkan.');
        })->name('works');

        Route::get('/works/preview/{theme}', function () {
            return redirect()->route('module.pagi.dashboard')->with('error', 'Fitur Works sementara dinonaktifkan.');
        })->name('works.preview');

        Route::get('/works/editor/{theme}', function () {
            return redirect()->route('module.pagi.dashboard')->with('error', 'Fitur Works sementara dinonaktifkan.');
        })->name('works.editor');

        Route::post('/works/save', function () {
            return redirect()->route('module.pagi.dashboard')->with('error', 'Fitur Works sementara dinonaktifkan.');
        })->name('works.save');

        Route::post('/works/select', function () {
            return redirect()->route('module.pagi.dashboard')->with('error', 'Fitur Works sementara dinonaktifkan.');
        })->name('works.select');

        // ── CV Builder Routes ─────────────────────────────────────────────────
        Route::get('/cv', [PagiCvController::class, 'index'])
            ->name('cv.index');
        Route::get('/cv/templates', [PagiCvController::class, 'templates'])
            ->name('cv.templates');
        Route::get('/cv/profile-data', [PagiCvController::class, 'getProfileData'])
            ->name('cv.profile-data');
        Route::post('/cv', [PagiCvController::class, 'store'])
            ->name('cv.store');
        Route::get('/cv/{cv}/edit', [PagiCvController::class, 'edit'])
            ->name('cv.edit');
        Route::put('/cv/{cv}', [PagiCvController::class, 'update'])
            ->name('cv.update');
        Route::post('/cv/{cv}/duplicate', [PagiCvController::class, 'duplicate'])
            ->name('cv.duplicate');
        Route::delete('/cv/{cv}', [PagiCvController::class, 'destroy'])
            ->name('cv.destroy');
        Route::post('/cv/{cv}/upload-photo', [PagiCvController::class, 'uploadPhoto'])
            ->middleware('throttle:uploads')
            ->name('cv.upload-photo');
        Route::get('/cv/{cv}/download', [PagiCvController::class, 'downloadPdf'])
            ->name('cv.download');

        // ── Chat / Messages (Realtime via Laravel Reverb) ──────────────────────
        Route::get('/messages', [PagiChatController::class, 'index'])
            ->name('messages');
        Route::get('/messages/contacts', [PagiChatController::class, 'contacts'])
            ->name('messages.contacts');
        Route::get('/messages/{partner}', [PagiChatController::class, 'show'])
            ->name('messages.show');
        Route::post('/messages', [PagiChatController::class, 'store'])
            ->middleware('throttle:pagi-chat-send')
            ->name('messages.store');
        Route::post('/messages/read', [PagiChatController::class, 'markAsRead'])
            ->name('messages.read');
        Route::post('/messages/pubkey', [PagiChatController::class, 'updatePublicKey'])
            ->name('messages.pubkey');
        Route::delete('/messages/{message}', [PagiChatController::class, 'destroy'])
            ->name('messages.destroy');
        Route::patch('/messages/{message}', [PagiChatController::class, 'update'])
            ->name('messages.update');
        Route::post('/messages/{message}/react', [PagiChatController::class, 'react'])
            ->name('messages.react');
        Route::delete('/messages/clear-all/conversation', [PagiChatController::class, 'clearChat'])
            ->name('messages.clear');
        Route::post('/messages/pin', [PagiChatController::class, 'togglePinConversation'])
            ->name('messages.pin');
        Route::post('/messages/archive', [PagiChatController::class, 'toggleArchiveConversation'])
            ->name('messages.archive');
        Route::post('/messages/unread', [PagiChatController::class, 'toggleReadConversation'])
            ->name('messages.unread');
        Route::delete('/messages/conversation/delete', [PagiChatController::class, 'deleteConversation'])
            ->name('messages.delete-conversation');
        Route::post('/messages/block', [PagiChatController::class, 'blockUser'])
            ->name('messages.block');
        Route::post('/messages/unblock', [PagiChatController::class, 'unblockUser'])
            ->name('messages.unblock');

        // Halaman Notifications — dapat diakses semua role user di modul PAGI
        Route::get('/notifications', [PagiDashboardController::class, 'notifications'])
            ->name('notifications');
        Route::post('/notifications/mark-all-read', [PagiDashboardController::class, 'markAllNotificationsRead'])
            ->name('notifications.mark-all-read');
        Route::post('/notifications/{id}/mark-read', [PagiDashboardController::class, 'markNotificationRead'])
            ->name('notifications.mark-read');
        Route::delete('/notifications/clear-all', [PagiDashboardController::class, 'clearAllNotifications'])
            ->name('notifications.clear-all');
        Route::delete('/notifications/{id}', [PagiDashboardController::class, 'deleteNotification'])
            ->name('notifications.delete');

        // Editor Routes
        Route::get('/editor', [PagiEditorController::class, 'editor'])
            ->name('editor');
        Route::post('/editor', [PagiEditorController::class, 'store'])
            ->middleware('throttle:uploads')
            ->name('editor.store');
        Route::post('/editor/quick-store', [PagiEditorController::class, 'quickStore'])
            ->name('editor.quick-store');
        Route::post('/gallery/store', [PagiEditorController::class, 'storeGalleryItem'])
            ->middleware('throttle:uploads')
            ->name('gallery.store');
        Route::post('/editor/{editor}/quick-update', [PagiEditorController::class, 'quickUpdate'])
            ->name('editor.quick-update');
        Route::post('/editor/{editor}', [PagiEditorController::class, 'update'])
            ->middleware('throttle:uploads')
            ->name('editor.update');
        Route::delete('/editor/{editor}', [PagiEditorController::class, 'destroy'])
            ->name('editor.destroy');
        Route::post('/editor/{editor}/collaboration/accept', [PagiEditorController::class, 'acceptCollaboration'])
            ->name('editor.collaboration.accept');
        Route::post('/editor/{editor}/collaboration/decline', [PagiEditorController::class, 'declineCollaboration'])
            ->name('editor.collaboration.decline');

        // User Profile
        Route::get('/profile', [PagiDashboardController::class, 'profile'])
            ->name('profile');
        Route::get('/profile/edit', [PagiDashboardController::class, 'editProfile'])
            ->name('profile.edit');
        Route::post('/profile/update', [PagiDashboardController::class, 'updateProfile'])
            ->middleware('throttle:uploads')
            ->name('profile.update');
        Route::get('/settings', [PagiDashboardController::class, 'settings'])
            ->name('settings');
        Route::post('/settings/update', [PagiDashboardController::class, 'updateSettings'])
            ->name('settings.update');
        Route::post('/certificates', [PagiDashboardController::class, 'storeCertificate'])
            ->middleware('throttle:uploads')
            ->name('certificates.store');
        Route::get('/certificates/org-logo', [PagiDashboardController::class, 'getOrgLogo'])
            ->name('certificates.org-logo');
        Route::post('/certificates/org-logo', [PagiDashboardController::class, 'uploadOrgLogo'])
            ->middleware('throttle:uploads')
            ->name('certificates.org-logo.upload');
        Route::match(['PUT', 'POST'], '/certificates/{id}', [PagiDashboardController::class, 'updateCertificate'])
            ->middleware('throttle:uploads')
            ->name('certificates.update');
        Route::delete('/certificates/{id}', [PagiDashboardController::class, 'destroyCertificate'])
            ->name('certificates.destroy');
        Route::post('/education', [PagiDashboardController::class, 'storeEducation'])
            ->name('education.store');
        Route::put('/education/{id}', [PagiDashboardController::class, 'updateEducation'])
            ->name('education.update');
        Route::delete('/education/{id}', [PagiDashboardController::class, 'destroyEducation'])
            ->name('education.destroy');
        Route::post('/profile/reorder-projects', [PagiDashboardController::class, 'reorderProjects'])
            ->name('profile.reorder-projects');
        Route::get('/username/check', [PagiDashboardController::class, 'checkUsername'])
            ->name('username.check');

        // Follow / Unfollow
        Route::post('/users/{user}/follow', [PagiDashboardController::class, 'toggleFollow'])
            ->name('users.follow');
        Route::get('/users/{user}/relations', [PagiDashboardController::class, 'getFollowRelations'])
            ->name('users.relations');
        Route::get('/users/search', [PagiDashboardController::class, 'searchUsers'])
            ->name('users.search');

        // Likes & Comments on Preview items
        // BUG-FE-001: throttled to prevent count inflation and DoS
        Route::post('/preview/{preview}/like', [PagiDashboardController::class, 'likePreview'])
            ->middleware('throttle:30,1')
            ->name('preview.like');
        Route::post('/preview/{preview}/comment', [PagiDashboardController::class, 'commentPreview'])
            ->middleware('throttle:20,1')
            ->name('preview.comment');
        Route::post('/preview/{preview}/comment/{comment}/like', [PagiDashboardController::class, 'likeComment'])
            ->middleware('throttle:30,1')
            ->name('preview.comment.like');
        Route::post('/preview/{preview}/comment/{comment}/reply', [PagiDashboardController::class, 'replyComment'])
            ->middleware('throttle:20,1')
            ->name('preview.comment.reply');
        Route::post('/preview/{preview}/comment/{comment}/reply/{reply}/like', [PagiDashboardController::class, 'likeReply'])
            ->middleware('throttle:30,1')
            ->name('preview.comment.reply.like');
        Route::post('/preview/{preview}/view', [PagiDashboardController::class, 'viewPreview'])
            ->middleware('throttle:60,1')
            ->name('preview.view');

        // Lazy-load endpoint — called only when user opens the project modal.
        // Returns the heavy payload (content blocks + comments) that is intentionally
        // excluded from the initial page load to keep the Inertia response small.
        Route::get('/preview/{preview}/data', [PagiDashboardController::class, 'previewData'])
            ->middleware('throttle:60,1')
            ->name('preview.data');

        // Report a work (karya mahasiswa)
        Route::post('/works/report', [AdminModerationController::class, 'storeReport'])
            ->name('works.report');
    });

// ── PAGI Admin Routes ────────────────────────────────────────────────────────
// SECURITY: dilindungi middleware auth, module.context:pagi dengan parameter role admin
Route::middleware(['auth', EnsureFirstTimeLoginComplete::class, 'module.context:pagi,super-admin,admin,admin-universitas,admin-akademik,prodi'])
    ->prefix('pagi/admin')
    ->name('module.pagi.admin.')
    ->group(function () {

        // Dashboard Overview
        Route::get('/', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // Moderasi Konten
        Route::get('/moderation', [AdminModerationController::class, 'moderation'])
            ->name('moderation');

        // Users Management
        Route::get('/users', [AdminUserController::class, 'index'])
            ->name('users.index');

        // Redirects untuk rute lama agar mencegah error 404/500
        Route::redirect('/users/mahasiswa', '/pagi/admin/users');
        Route::redirect('/users/mitra', '/pagi/admin/users');
        Route::redirect('/gallery', '/pagi/admin');
        Route::redirect('/logs', '/pagi/admin');
        Route::redirect('/roles', '/pagi/admin');

        // Analytics
        Route::get('/analytics', [AdminDashboardController::class, 'analytics'])
            ->name('analytics');

        // Reports
        Route::get('/reports', [AdminWorkController::class, 'reports'])
            ->name('reports');

        // Settings
        Route::get('/settings', [AdminDashboardController::class, 'settings'])
            ->name('settings');

        // Warnings
        Route::get('/warnings', [AdminWorkController::class, 'warnings'])
            ->name('warnings');

        // Takedowns
        Route::get('/takedowns', [AdminWorkController::class, 'takedowns'])
            ->name('takedowns');

        // Works
        Route::get('/works', [AdminWorkController::class, 'works'])
            ->name('works');

        // Tags
        Route::get('/tags', [AdminDashboardController::class, 'tags'])
            ->name('tags');

        // Moderation Actions — POST only for CSRF protection
        Route::post('/users/{user}/warn', [AdminUserController::class, 'warnUser'])
            ->name('users.warn');

        Route::post('/content/work/{work}/moderate', [AdminModerationController::class, 'hideContent'])
            ->name('content.moderate');

        // Admin send notification to work owner
        Route::post('/users/{user}/notify', [AdminUserController::class, 'sendNotificationToUser'])
            ->name('users.notify');

        // Reset moderation & warnings data
        Route::post('/reset-moderation', [AdminModerationController::class, 'resetModeration'])
            ->name('reset-moderation');

        // Revoke user warning
        Route::post('/warnings/{warning}/revoke', [AdminUserController::class, 'revokeWarning'])
            ->name('warnings.revoke');

        // Restore takedown work
        Route::post('/takedowns/{work}/restore', [AdminModerationController::class, 'restoreContent'])
            ->name('takedowns.restore');

        // Realtime stats polling endpoint (JSON)
        Route::get('/api/stats', [AdminDashboardController::class, 'apiStats'])
            ->name('api.stats');

        // Realtime chart data endpoint (JSON)
        Route::get('/api/chart', [AdminDashboardController::class, 'apiChart'])
            ->name('api.chart');

        // Fetch admin notifications endpoint (JSON)
        Route::get('/api/notifications', [AdminDashboardController::class, 'apiAdminNotifications'])
            ->name('api.notifications');
    });

// Public / Guest Profile Route

Route::prefix('pagi')
    ->name('module.pagi.')
    ->group(function () {
        Route::get('/works/v/{user:pagi_username}', function () {
            return redirect()->route('module.pagi.dashboard')->with('error', 'Fitur Works sementara dinonaktifkan.');
        })->name('works.view_public');
        Route::get('/profile/{user}', [PagiDashboardController::class, 'publicProfile'])
            ->name('profile.public');
        Route::get('/users/{user}/works', [PagiDashboardController::class, 'userWorks'])
            ->name('users.works');
        // SEC-004: CV share requires auth to prevent unauthenticated IDOR enumeration of all student CVs.
        // Signed URL or token-based sharing can be implemented in the future.
        Route::get('/cv/{cv}/shared', [PagiCvController::class, 'shareView'])
            ->middleware('auth')
            ->name('cv.shared');
        Route::get('/{user:pagi_username}/{tab?}', [PagiDashboardController::class, 'publicProfile'])
            ->name('profile.username');
    });
