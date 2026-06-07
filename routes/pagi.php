<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModuleDashboardController;
use App\Modules\Pagi\Controllers\AdminDashboardController;
use App\Modules\Pagi\Controllers\PagiChatController;
use App\Modules\Pagi\Controllers\PagiWorkController;
use App\Modules\Pagi\Controllers\PagiCvController;
use App\Modules\Pagi\Controllers\PagiEditorController;

Route::middleware(['auth', \App\Http\Middleware\EnsureFirstTimeLoginComplete::class, 'module.context:pagi'])
    ->prefix('pagi')
    ->name('module.pagi.')
    ->group(function () {
        Route::get('/', [ModuleDashboardController::class, 'index'])
             ->defaults('moduleCode', 'pagi')
             ->name('dashboard');

        // Halaman People — dapat diakses semua role user di modul PAGI
        Route::get('/people', [ModuleDashboardController::class, 'explorePeople'])
             ->name('people');

        // Halaman Gallery — menampung seluruh karya & galeri mahasiswa
        Route::get('/gallery', [ModuleDashboardController::class, 'exploreGallery'])
             ->name('gallery');

        // Halaman Works — Pemilihan Template
        Route::get('/works', [PagiWorkController::class, 'index'])
             ->name('works');
        Route::get('/works/preview/{theme}', [PagiWorkController::class, 'previewTheme'])
             ->name('works.preview');
        Route::get('/works/editor/{theme}', [PagiWorkController::class, 'editPortfolio'])
             ->name('works.editor');
        Route::post('/works/save', [PagiWorkController::class, 'savePortfolio'])
             ->name('works.save');
        Route::post('/works/select', [PagiWorkController::class, 'selectTemplate'])
             ->name('works.select');

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
        Route::get('/notifications', [ModuleDashboardController::class, 'notifications'])
             ->name('notifications');
        Route::post('/notifications/mark-all-read', [ModuleDashboardController::class, 'markAllNotificationsRead'])
             ->name('notifications.mark-all-read');
        Route::post('/notifications/{id}/mark-read', [ModuleDashboardController::class, 'markNotificationRead'])
             ->name('notifications.mark-read');
        Route::delete('/notifications/clear-all', [ModuleDashboardController::class, 'clearAllNotifications'])
             ->name('notifications.clear-all');
        Route::delete('/notifications/{id}', [ModuleDashboardController::class, 'deleteNotification'])
             ->name('notifications.delete');


        // Editor Routes
        Route::get('/editor', [PagiEditorController::class, 'editor'])
             ->name('editor');
        Route::post('/editor', [PagiEditorController::class, 'store'])
             ->name('editor.store');
        Route::post('/editor/quick-store', [PagiEditorController::class, 'quickStore'])
             ->name('editor.quick-store');
        Route::post('/gallery/store', [PagiEditorController::class, 'storeGalleryItem'])
             ->name('gallery.store');
        Route::post('/editor/{editor}/quick-update', [PagiEditorController::class, 'quickUpdate'])
             ->name('editor.quick-update');
        Route::post('/editor/{editor}', [PagiEditorController::class, 'update'])
             ->name('editor.update');
        Route::delete('/editor/{editor}', [PagiEditorController::class, 'destroy'])
             ->name('editor.destroy');
        Route::post('/editor/{editor}/collaboration/accept', [PagiEditorController::class, 'acceptCollaboration'])
             ->name('editor.collaboration.accept');
        Route::post('/editor/{editor}/collaboration/decline', [PagiEditorController::class, 'declineCollaboration'])
             ->name('editor.collaboration.decline');

        // User Profile
        Route::get('/profile', [ModuleDashboardController::class, 'profile'])
             ->name('profile');
        Route::get('/profile/edit', [ModuleDashboardController::class, 'editProfile'])
             ->name('profile.edit');
        Route::post('/profile/update', [ModuleDashboardController::class, 'updateProfile'])
             ->name('profile.update');
        Route::get('/settings', [ModuleDashboardController::class, 'settings'])
             ->name('settings');
        Route::post('/settings/update', [ModuleDashboardController::class, 'updateSettings'])
             ->name('settings.update');
        Route::post('/certificates', [ModuleDashboardController::class, 'storeCertificate'])
             ->name('certificates.store');
        Route::get('/certificates/org-logo', [ModuleDashboardController::class, 'getOrgLogo'])
             ->name('certificates.org-logo');
        Route::post('/certificates/org-logo', [ModuleDashboardController::class, 'uploadOrgLogo'])
             ->name('certificates.org-logo.upload');
        Route::match(['PUT', 'POST'], '/certificates/{id}', [ModuleDashboardController::class, 'updateCertificate'])
             ->name('certificates.update');
        Route::delete('/certificates/{id}', [ModuleDashboardController::class, 'destroyCertificate'])
             ->name('certificates.destroy');
        Route::post('/profile/reorder-projects', [ModuleDashboardController::class, 'reorderProjects'])
             ->name('profile.reorder-projects');
        Route::get('/username/check', [ModuleDashboardController::class, 'checkUsername'])
             ->name('username.check');

        // Follow / Unfollow
        Route::post('/users/{user}/follow', [ModuleDashboardController::class, 'toggleFollow'])
             ->name('users.follow');
        Route::get('/users/{user}/relations', [ModuleDashboardController::class, 'getFollowRelations'])
             ->name('users.relations');
        Route::get('/users/search', [ModuleDashboardController::class, 'searchUsers'])
             ->name('users.search');

        // Likes & Comments on Preview items
        Route::post('/preview/{preview}/like', [ModuleDashboardController::class, 'likePreview'])
             ->name('preview.like');
        Route::post('/preview/{preview}/comment', [ModuleDashboardController::class, 'commentPreview'])
             ->name('preview.comment');
        Route::post('/preview/{preview}/comment/{comment}/like', [ModuleDashboardController::class, 'likeComment'])
             ->name('preview.comment.like');
        Route::post('/preview/{preview}/comment/{comment}/reply', [ModuleDashboardController::class, 'replyComment'])
             ->name('preview.comment.reply');
        Route::post('/preview/{preview}/comment/{comment}/reply/{reply}/like', [ModuleDashboardController::class, 'likeReply'])
             ->name('preview.comment.reply.like');
        Route::post('/preview/{preview}/view', [ModuleDashboardController::class, 'viewPreview'])
             ->name('preview.view');

        // Report a work (karya mahasiswa)
        Route::post('/works/report', [AdminDashboardController::class, 'storeReport'])
             ->name('works.report');
    });

// ── PAGI Admin Routes ────────────────────────────────────────────────────────
// SECURITY: dilindungi middleware auth, module.context:pagi dengan parameter role admin
Route::middleware(['auth', \App\Http\Middleware\EnsureFirstTimeLoginComplete::class, 'module.context:pagi,super-admin,admin,admin-universitas,admin-akademik,prodi'])
    ->prefix('pagi/admin')
    ->name('module.pagi.admin.')
    ->group(function () {

        // Dashboard Overview
        Route::get('/', [AdminDashboardController::class, 'index'])
             ->name('dashboard');

        // Moderasi Konten
        Route::get('/moderation', [AdminDashboardController::class, 'moderation'])
             ->name('moderation');

        // Users Management
        Route::get('/users/mahasiswa', [AdminDashboardController::class, 'mahasiswa'])
             ->name('users.mahasiswa');
        
        Route::get('/users/mitra', [AdminDashboardController::class, 'mitra'])
             ->name('users.mitra');

        // Analytics
        Route::get('/analytics', [AdminDashboardController::class, 'analytics'])
             ->name('analytics');

        // Reports
        Route::get('/reports', [AdminDashboardController::class, 'reports'])
             ->name('reports');

        // Settings
        Route::get('/settings', [AdminDashboardController::class, 'settings'])
             ->name('settings');

        // Activity Logs
        Route::get('/logs', [AdminDashboardController::class, 'logs'])
             ->name('logs');

        // Warnings
        Route::get('/warnings', [AdminDashboardController::class, 'warnings'])
             ->name('warnings');

        // Takedowns
        Route::get('/takedowns', [AdminDashboardController::class, 'takedowns'])
             ->name('takedowns');

        // Works
        Route::get('/works', [AdminDashboardController::class, 'works'])
             ->name('works');

        // Gallery
        Route::get('/gallery', [AdminDashboardController::class, 'gallery'])
             ->name('gallery');

        // Tags
        Route::get('/tags', [AdminDashboardController::class, 'tags'])
             ->name('tags');

        // Roles
        Route::get('/roles', [AdminDashboardController::class, 'roles'])
             ->name('roles');

        // Moderation Actions — POST only for CSRF protection
        Route::post('/users/{user}/warn', [AdminDashboardController::class, 'warnUser'])
             ->name('users.warn');

        Route::post('/content/work/{work}/moderate', [AdminDashboardController::class, 'hideContent'])
             ->name('content.moderate');

        // Admin send notification to work owner
        Route::post('/users/{user}/notify', [AdminDashboardController::class, 'sendNotificationToUser'])
             ->name('users.notify');

        // Reset moderation & warnings data
        Route::post('/reset-moderation', [AdminDashboardController::class, 'resetModeration'])
             ->name('reset-moderation');

        // Revoke user warning
        Route::post('/warnings/{warning}/revoke', [AdminDashboardController::class, 'revokeWarning'])
             ->name('warnings.revoke');

        // Restore takedown work
        Route::post('/takedowns/{work}/restore', [AdminDashboardController::class, 'restoreContent'])
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
        Route::get('/works/v/{user:pagi_username}', [PagiWorkController::class, 'viewPublicPortfolio'])
             ->name('works.view_public');
        Route::get('/profile/{user}', [ModuleDashboardController::class, 'publicProfile'])
             ->name('profile.public');
        Route::get('/users/{user}/works', [ModuleDashboardController::class, 'userWorks'])
             ->name('users.works');
        Route::get('/cv/{cv}/shared', [PagiCvController::class, 'shareView'])
             ->name('cv.shared');
        Route::get('/{user:pagi_username}/{tab?}', [ModuleDashboardController::class, 'publicProfile'])
             ->name('profile.username');
    });

