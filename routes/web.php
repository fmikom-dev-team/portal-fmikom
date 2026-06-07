<?php

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

use App\Modules\WorkOs\Controllers\Auth\FirstTimeLoginController;
use App\Http\Middleware\EnsureFirstTimeLoginComplete;
use App\Modules\Coreportal\Controllers\PortalController;

/*
|--------------------------------------------------------------------------
| Web Routes — Core Application
|--------------------------------------------------------------------------
|
| This file contains only the core application routes.
| Modular routes are auto-loaded via bootstrap/app.php:
|   → routes/auth/oauth.php        (public — no auth)
|   → routes/auth/passkeys.php     (public — no auth)
|   → routes/auth/magic-links.php  (public — signed URLs)
|   → routes/auth/sso.php          (public SAM ACS + admin management)
|   → routes/auth/sessions.php     (protected — auth)
|   → routes/auth/mfa.php          (protected — auth)
|   → routes/workos/*.php          (admin — auth + super-admin)
|
*/

// ─── Public Pages ────────────────────────────────────────────────────────────

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    $settings = \Illuminate\Support\Facades\Cache::rememberForever('portal_settings', function () {
        return \App\Models\PortalSetting::pluck('value', 'key')->toArray();
    });

    $settings['hero_gallery'] = isset($settings['hero_gallery'])
        ? json_decode($settings['hero_gallery'], true) : [];
    $settings['partners'] = isset($settings['partners'])
        ? json_decode($settings['partners'], true) : [];

    $latest_posts = \Illuminate\Support\Facades\Cache::remember('portal_latest_posts', 3600, function () {
        return \App\Models\PortalPost::with('user:id,name')
            ->select(['id', 'title', 'slug', 'meta_description', 'thumbnail', 'published_at', 'created_at', 'user_id', 'status'])
            ->where('status', \App\Models\PortalPost::STATUS_PUBLISHED)
            ->latest()
            ->take(5)
            ->get();
    });

    return \Inertia\Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'settings' => $settings,
        'latest_posts' => $latest_posts,
    ]);
})->name('home');

Route::get('/berita', [\App\Modules\Portal\Controllers\PublicPostController::class, 'index'])->name('portal.posts.index');
Route::get('/berita/{slug}', [\App\Modules\Portal\Controllers\PublicPostController::class, 'show'])->name('portal.posts.show');
Route::post('/berita/{slug}/comments', [\App\Modules\Portal\Controllers\PublicPostController::class, 'storeComment'])->name('portal.posts.comments.store');
Route::get('/halaman/{slug}', [\App\Modules\Portal\Controllers\PublicPageController::class, 'show'])->name('portal.pages.show');

// API: Unique check for registration (throttled)
Route::post('/api/check-user-exists', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'email'       => ['nullable', 'email', 'max:255'],
        'nomor_induk' => ['nullable', 'string', 'max:50'],
    ]);

    return response()->json([
        'email_exists'       => $request->email
            ? \App\Models\User::where('email', $request->email)->exists() : false,
        'nomor_induk_exists' => $request->nomor_induk
            ? \App\Models\User::where('nomor_induk', $request->nomor_induk)->exists() : false,
    ]);
})->middleware('throttle:5,1');

// ─── Authenticated Routes ────────────────────────────────────────────────────

// ─── MFA Login Intercept (Overrides Fortify) ───────────────────────────────
Route::post('/two-factor-challenge', [\App\Modules\WorkOs\Controllers\Auth\TwoFactorChallengeController::class, 'store'])
    ->middleware(['guest', 'throttle:two-factor'])
    ->name('two-factor.login.store');

// ─── Radar Shield on Login ─────────────────────────────────────────────────
// Intercept Fortify's POST /login to run bot detection, brute-force checks, etc.
Route::middleware(['web', 'radar.shield'])
    ->group(function () {
        Route::post('/login', function (\Laravel\Fortify\Http\Requests\LoginRequest $request) {
            // After radar.shield runs, pass through to Fortify's handler
            return app(\Laravel\Fortify\Http\Controllers\AuthenticatedSessionController::class)->store($request);
        })->middleware('throttle:login')->name('login.store');
    });

Route::middleware(['auth'])->group(function () {

    // OTP Verification (First-time login flow)
    Route::get('/verify-otp', [FirstTimeLoginController::class, 'showOtpForm'])->name('verify.otp');
    Route::post('/verify-otp', [FirstTimeLoginController::class, 'verifyOtp'])->middleware('throttle:5,1');
    Route::post('/resend-otp', [FirstTimeLoginController::class, 'resendOtp'])->middleware('throttle:3,5')->name('resend.otp');

    Route::get('/force-change-password', [FirstTimeLoginController::class, 'showPasswordForm'])->name('password.force.change');
    Route::post('/force-change-password', [FirstTimeLoginController::class, 'updatePassword'])->middleware('throttle:10,1');

    // Core Application (post-OTP verification)
    Route::middleware([EnsureFirstTimeLoginComplete::class])->group(function () {
        Route::get('/dashboard', [PortalController::class, 'index'])->name('dashboard');
        Route::post('/select-module', [PortalController::class, 'selectModule'])->name('module.select');
        Route::post('/portal/switch-role', [PortalController::class, 'switchRole'])->name('portal.switch-role');
        Route::inertia('/portal', 'Portal')->name('portal');
    });
});

// ─── Module Routes ───────────────────────────────────────────────────────────

require __DIR__.'/pagi.php';
require __DIR__.'/wims.php';
require __DIR__.'/fast.php';
require __DIR__.'/trace.php';

// ─── Portal Admin ────────────────────────────────────────────────────────────

Route::middleware(['auth', \App\Http\Middleware\CheckRole::class . ':super-admin'])
    ->prefix('portal-admin')
    ->name('portal-admin.')
    ->group(function () {
        Route::get('/', [\App\Modules\Portal\Controllers\PortalAdminController::class, 'index'])->name('dashboard');
        Route::resource('posts', \App\Modules\Portal\Controllers\PortalPostController::class);
        Route::post('posts/upload-image', [\App\Modules\Portal\Controllers\PortalPostController::class, 'uploadImage'])->name('posts.upload-image');
        Route::get('fetchUrl', [\App\Modules\Portal\Controllers\PortalPostController::class, 'fetchUrl'])->name('posts.fetch-url');
        Route::post('posts/upload-file', [\App\Modules\Portal\Controllers\PortalPostController::class, 'uploadFile'])->name('posts.upload-file');
        Route::resource('categories', \App\Modules\Portal\Controllers\PortalCategoryController::class);
        Route::resource('media', \App\Modules\Portal\Controllers\PortalMediaController::class);
        Route::resource('pages', \App\Modules\Portal\Controllers\PortalPageController::class);
        Route::resource('academic-calendars', \App\Modules\Portal\Controllers\PortalAcademicCalendarController::class);
        Route::resource('events', \App\Modules\Portal\Controllers\PortalEventController::class);
        Route::resource('comments', \App\Modules\Portal\Controllers\PortalCommentController::class)->only(['index', 'update', 'destroy']);
        Route::post('menus/reorder', [\App\Modules\Portal\Controllers\PortalMenuController::class, 'reorder'])->name('menus.reorder');
        Route::resource('menus', \App\Modules\Portal\Controllers\PortalMenuController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/appearance', [\App\Modules\Portal\Controllers\PortalAdminController::class, 'appearance'])->name('appearance');
        Route::post('/appearance', [\App\Modules\Portal\Controllers\PortalAdminController::class, 'updateAppearance'])->name('appearance.update');
        Route::get('/settings', [\App\Modules\Portal\Controllers\PortalAdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [\App\Modules\Portal\Controllers\PortalAdminController::class, 'updateSettings'])->name('settings.update');
    });

// ─── Dev-only Seed Route ─────────────────────────────────────────────────────

if (app()->isLocal() || app()->environment('testing')) {
    Route::get('/seed-dummy', function () {
        \App\Models\Role::firstOrCreate(['slug' => 'super-admin'], ['nama' => 'Super Admin']);
        \App\Models\Role::firstOrCreate(['slug' => 'user'], ['nama' => 'User / Mahasiswa']);

        \App\Models\User::updateOrCreate(
            ['email' => 'muchlisinmaruf@gmail.com'],
            ['name' => 'Muchlisin Maruf', 'password' => \Illuminate\Support\Facades\Hash::make('admin123'), 'user_type' => 'super_admin', 'email_verified_at' => now(), 'password_changed_at' => now()]
        );
        \App\Models\User::updateOrCreate(
            ['email' => 'mahasiswa@example.com'],
            ['name' => 'Dummy Pelajar', 'password' => \Illuminate\Support\Facades\Hash::make('mahasiswa123'), 'user_type' => 'mahasiswa', 'email_verified_at' => now(), 'password_changed_at' => now()]
        );

        return 'Seeding Completed — only runs in local/testing environment.';
    });
}

// ─── Invitation Accept (Public) ───────────────────────────────────────────────
Route::get('/invitations/accept/{token}', [\App\Modules\WorkOs\Controllers\InvitationAcceptController::class, 'show'])
    ->name('invitations.accept');

// ─── Image Proxy Obfuscated Streaming ──────────────────────────────────────────
Route::get('/images/v1/{encrypted_path}', [\App\Modules\WorkOs\Controllers\ImageProxyController::class, 'serve'])
    ->name('images.proxy');

require __DIR__.'/settings.php';
