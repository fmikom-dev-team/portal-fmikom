<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureFirstTimeLoginComplete;
use App\Models\Portal\PortalPost;
use App\Models\Portal\PortalSetting;
use App\Models\Role;
use App\Models\User;
use App\Modules\Coreportal\Controllers\PortalController;
use App\Modules\Portal\Controllers\PortalAcademicCalendarController;
use App\Modules\Portal\Controllers\PortalAdminController;
use App\Modules\Portal\Controllers\PortalCategoryController;
use App\Modules\Portal\Controllers\PortalCommentController;
use App\Modules\Portal\Controllers\PortalEventController;
use App\Modules\Portal\Controllers\PortalMediaController;
use App\Modules\Portal\Controllers\PortalMenuController;
use App\Modules\Portal\Controllers\PortalPageController;
use App\Modules\Portal\Controllers\PortalPostController;
use App\Modules\Portal\Controllers\PublicPageController;
use App\Modules\Portal\Controllers\PublicPostController;
use App\Modules\WorkOs\Controllers\Auth\FirstTimeLoginController;
use App\Modules\WorkOs\Controllers\Auth\TwoFactorChallengeController;
use App\Modules\WorkOs\Controllers\ImageProxyController;
use App\Modules\WorkOs\Controllers\InvitationAcceptController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Requests\LoginRequest;

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

    $settings = Cache::rememberForever('portal_settings', function () {
        return PortalSetting::pluck('value', 'key')->toArray();
    });

    $settings['hero_gallery'] = isset($settings['hero_gallery'])
        ? json_decode($settings['hero_gallery'], true) : [];
    $settings['partners'] = isset($settings['partners'])
        ? json_decode($settings['partners'], true) : [];

    $latest_posts = Cache::remember('portal_latest_posts', 3600, function () {
        return PortalPost::with('user:id,name')
            ->select(['id', 'title', 'slug', 'meta_description', 'thumbnail', 'published_at', 'created_at', 'user_id', 'status'])
            ->where('status', PortalPost::STATUS_PUBLISHED)
            ->latest()
            ->take(5)
            ->get();
    });

    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'settings' => $settings,
        'latest_posts' => $latest_posts,
    ]);
})->name('home');

Route::get('/berita', [PublicPostController::class, 'index'])->name('portal.posts.index');
Route::get('/berita/{slug}', [PublicPostController::class, 'show'])->name('portal.posts.show');
Route::post('/berita/{slug}/comments', [PublicPostController::class, 'storeComment'])->name('portal.posts.comments.store');
Route::get('/halaman/{slug}', [PublicPageController::class, 'show'])->name('portal.pages.show');

// API: Unique check for registration (throttled)
Route::post('/api/check-user-exists', function (Request $request) {
    $request->validate([
        'email' => ['nullable', 'email', 'max:255'],
        'nomor_induk' => ['nullable', 'string', 'max:50'],
    ]);

    return response()->json([
        'email_exists' => $request->email
            ? User::where('email', $request->email)->exists() : false,
        'nomor_induk_exists' => $request->nomor_induk
            ? User::where('nomor_induk', $request->nomor_induk)->exists() : false,
    ]);
})->middleware('throttle:5,1');

// ─── Authenticated Routes ────────────────────────────────────────────────────

// ─── MFA Login Intercept (Overrides Fortify) ───────────────────────────────
Route::post('/two-factor-challenge', [TwoFactorChallengeController::class, 'store'])
    ->middleware(['guest', 'throttle:two-factor'])
    ->name('two-factor.login.store');

// ─── Radar Shield on Login ─────────────────────────────────────────────────
// Intercept Fortify's POST /login to run bot detection, brute-force checks, etc.
Route::middleware(['web', 'radar.shield'])
    ->group(function () {
        Route::post('/login', function (LoginRequest $request) {
            // After radar.shield runs, pass through to Fortify's handler
            return app(AuthenticatedSessionController::class)->store($request);
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

Route::middleware(['auth', CheckRole::class.':super-admin'])
    ->prefix('portal-admin')
    ->name('portal-admin.')
    ->group(function () {
        Route::get('/', [PortalAdminController::class, 'index'])->name('dashboard');
        Route::resource('posts', PortalPostController::class);
        Route::post('posts/upload-image', [PortalPostController::class, 'uploadImage'])->name('posts.upload-image');
        Route::get('fetchUrl', [PortalPostController::class, 'fetchUrl'])->name('posts.fetch-url');
        Route::post('posts/upload-file', [PortalPostController::class, 'uploadFile'])->name('posts.upload-file');
        Route::resource('categories', PortalCategoryController::class);
        Route::resource('media', PortalMediaController::class);
        Route::resource('pages', PortalPageController::class);
        Route::resource('academic-calendars', PortalAcademicCalendarController::class);
        Route::resource('events', PortalEventController::class);
        Route::resource('comments', PortalCommentController::class)->only(['index', 'update', 'destroy']);
        Route::post('menus/reorder', [PortalMenuController::class, 'reorder'])->name('menus.reorder');
        Route::resource('menus', PortalMenuController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/appearance', [PortalAdminController::class, 'appearance'])->name('appearance');
        Route::post('/appearance', [PortalAdminController::class, 'updateAppearance'])->name('appearance.update');
        Route::get('/settings', [PortalAdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [PortalAdminController::class, 'updateSettings'])->name('settings.update');
    });

// ─── Dev-only Seed Route ─────────────────────────────────────────────────────

if (app()->isLocal() || app()->environment('testing')) {
    Route::get('/seed-dummy', function () {
        Role::firstOrCreate(['slug' => 'super-admin'], ['nama' => 'Super Admin']);
        Role::firstOrCreate(['slug' => 'user'], ['nama' => 'User / Mahasiswa']);

        User::updateOrCreate(
            ['email' => 'muchlisinmaruf@gmail.com'],
            ['name' => 'Muchlisin Maruf', 'password' => Hash::make('admin123'), 'user_type' => 'super_admin', 'email_verified_at' => now(), 'password_changed_at' => now()]
        );
        User::updateOrCreate(
            ['email' => 'mahasiswa@example.com'],
            ['name' => 'Dummy Pelajar', 'password' => Hash::make('mahasiswa123'), 'user_type' => 'mahasiswa', 'email_verified_at' => now(), 'password_changed_at' => now()]
        );

        return 'Seeding Completed — only runs in local/testing environment.';
    });
}

// ─── Invitation Accept (Public) ───────────────────────────────────────────────
Route::get('/invitations/accept/{token}', [InvitationAcceptController::class, 'show'])
    ->name('invitations.accept');

// ─── Image Proxy Obfuscated Streaming ──────────────────────────────────────────
Route::get('/images/v1/{encrypted_path}', [ImageProxyController::class, 'serve'])
    ->name('images.proxy');

require __DIR__.'/settings.php';
