<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureFirstTimeLoginComplete;
use App\Models\Portal\PortalDocument;
use App\Models\Portal\PortalPost;
use App\Models\Portal\PortalSetting;
use App\Models\Role;
use App\Models\Tracer\Employment;
use App\Models\Tracer\ProfilAlumni;
use App\Models\User;
use App\Modules\Coreportal\Controllers\PortalController;
use App\Modules\Portal\Controllers\PortalAcademicCalendarController;
use App\Modules\Portal\Controllers\PortalAdminController;
use App\Modules\Portal\Controllers\PortalCategoryController;
use App\Modules\Portal\Controllers\PortalCommentController;
use App\Modules\Portal\Controllers\PortalDocumentController;
use App\Modules\Portal\Controllers\PortalEventController;
use App\Modules\Portal\Controllers\PortalMediaController;
use App\Modules\Portal\Controllers\PortalMenuController;
use App\Modules\Portal\Controllers\PortalPageController;
use App\Modules\Portal\Controllers\PortalPostController;
use App\Modules\Portal\Controllers\PublicDocumentController;
use App\Modules\Portal\Controllers\PublicEventController;
use App\Modules\Portal\Controllers\PublicPageController;
use App\Modules\Portal\Controllers\PublicPostController;
use App\Modules\WorkOs\Controllers\Auth\ActivationConfirmController;
use App\Modules\WorkOs\Controllers\Auth\ActivationController;
use App\Modules\WorkOs\Controllers\Auth\FirstTimeLoginController;
use App\Modules\WorkOs\Controllers\Auth\TwoFactorChallengeController;
use App\Modules\WorkOs\Controllers\ImageProxyController;
use App\Modules\WorkOs\Controllers\InvitationAcceptController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Requests\LoginRequest;
use App\Http\Controllers\PwaController;

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
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    $settings = Cache::rememberForever('portal_settings', function () {
        return PortalSetting::pluck('value', 'key')->toArray();
    });

    $settings['hero_gallery'] = isset($settings['hero_gallery'])
        ? json_decode($settings['hero_gallery'], true) : [];
    $settings['partners'] = isset($settings['partners'])
        ? json_decode($settings['partners'], true) : [];

    $latest_posts = Inertia::defer(fn () => Cache::remember('portal_latest_posts', 3600, function () {
        return PortalPost::with('user:id,name')
            ->select(['id', 'title', 'slug', 'excerpt', 'meta_description', 'content', 'thumbnail', 'published_at', 'created_at', 'user_id', 'status'])
            ->where('status', PortalPost::STATUS_PUBLISHED)
            ->latest()
            ->take(5)
            ->get();
    }));

    $pinned_announcement = Cache::remember('portal_pinned_announcement', 3600, function () {
        return PortalDocument::where('is_pinned', '=', true, 'and')
            ->latest()
            ->first();
    });

    $total_alumni = Cache::remember('portal_total_alumni', 600, function () {
        try {
            return ProfilAlumni::count('*');
        } catch (Throwable $e) {
            return 0;
        }
    });

    $alumni_data = Cache::remember('portal_welcome_alumni_data', 600, function () {
        try {
            return ProfilAlumni::with([
                'user:id,name,tahun_lulus,foto_path',
                'provinsi:id,name',
                'kota:id,name',
                'careers' => function ($query) {
                    $query->where('is_current', true)
                        ->whereIn('status', ['bekerja', 'wirausaha', 'lanjut_studi', 'mencari_kerja'])
                        ->with([
                            'employment:career_history_id,nama_perusahaan,jabatan',
                            'education:career_history_id,nama_universitas,program_studi_lanjutan',
                        ]);
                },
            ])
                ->whereHas('user')
                ->where(function ($query) {
                    $query->whereHas('careers', function ($careerQuery) {
                        $careerQuery->where('is_current', true)
                            ->whereIn('status', ['bekerja', 'wirausaha', 'lanjut_studi', 'mencari_kerja']);
                    })->orWhereDoesntHave('careers', function ($careerQuery) {
                        $careerQuery->where('is_current', true);
                    });
                })
                ->get()
                ->map(function ($alumni) {
                    $currentCareer = $alumni->careers->first();
                    $careerInfo = '';
                    if ($currentCareer) {
                        if ($currentCareer->status->value === 'bekerja') {
                            $emp = $currentCareer->employment;
                            $careerInfo = $emp ? ($emp->jabatan.' di '.$emp->nama_perusahaan) : 'Bekerja';
                        } elseif ($currentCareer->status->value === 'wirausaha') {
                            $emp = $currentCareer->employment;
                            $careerInfo = $emp ? ('Wirausaha ('.$emp->nama_perusahaan.')') : 'Wirausaha';
                        } elseif ($currentCareer->status->value === 'lanjut_studi') {
                            $edu = $currentCareer->education;
                            $careerInfo = $edu ? ('Lanjut Studi di '.$edu->nama_universitas) : 'Lanjut Studi';
                        } elseif ($currentCareer->status->value === 'mencari_kerja') {
                            $careerInfo = 'Mencari kerja / belum bekerja';
                        }
                    }

                    $lat = (! empty($currentCareer?->latitude) && $currentCareer->latitude != 0) ? $currentCareer->latitude : $alumni->latitude_rumah;
                    $lng = (! empty($currentCareer?->longitude) && $currentCareer->longitude != 0) ? $currentCareer->longitude : $alumni->longitude_rumah;

                    return [
                        'id' => $alumni->id,
                        'name' => $alumni->user->name,
                        'tahun_lulus' => $alumni->user->tahun_lulus ?? $alumni->angkatan,
                        'foto_path' => $alumni->user->foto_path,
                        'provinsi' => $alumni->provinsi?->name,
                        'kota' => $alumni->kota?->name,
                        'status' => $currentCareer?->status?->value === 'mencari_kerja' || ! $currentCareer ? 'belum' : $currentCareer?->status?->value,
                        'detail_karir' => $careerInfo ?: 'Belum bekerja',
                        'latitude' => $lat,
                        'longitude' => $lng,
                    ];
                })
                ->filter(fn ($alumni) => ! empty($alumni['latitude']) && ! empty($alumni['longitude']) && (float) $alumni['latitude'] !== 0.0 && (float) $alumni['longitude'] !== 0.0)
                ->values()
                ->toArray();
        } catch (Throwable $e) {
            return [];
        }
    });

    $alumni_stats = Cache::remember('portal_welcome_alumni_stats', 600, function () {
        try {
            $alumni = ProfilAlumni::count('*');
            $provinsi = ProfilAlumni::select('provinsi_id')->distinct()->whereNotNull('provinsi_id', 'and', true)->count('*');
            $perusahaan = Employment::select('nama_perusahaan')->distinct()->whereNotNull('nama_perusahaan', 'and', true)->count('*');
            $luarNegeri = ProfilAlumni::whereNull('provinsi_id', 'and', false)->whereNotNull('alamat_rumah', 'and', true)->count('*');

            return [
                'alumni' => $alumni,
                'provinsi' => $provinsi,
                'perusahaan' => $perusahaan,
                'luarNegeri' => $luarNegeri,
            ];
        } catch (Throwable $e) {
            return [
                'alumni' => 0,
                'provinsi' => 0,
                'perusahaan' => 0,
                'luarNegeri' => 0,
            ];
        }
    });

    $events = Cache::remember('portal_welcome_events', 600, function () {
        try {
            $now = now();
            return \App\Models\Portal\PortalEvent::where(function ($q) use ($now) {
                $q->where('status', 'published')
                    ->orWhere(function ($sq) use ($now) {
                        $sq->where('status', 'scheduled')
                            ->where('published_at', '<=', $now);
                    });
            })
            ->where(function ($q) use ($now) {
                $q->where(function ($inner) use ($now) {
                    $inner->whereNull('end_time')->where('start_time', '>=', $now);
                })->orWhere(function ($inner) use ($now) {
                    $inner->whereNotNull('end_time')->where('end_time', '>=', $now);
                });
            })
            ->orderBy('start_time', 'asc')
            ->take(3)
            ->get()
            ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    });

    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'settings' => $settings,
        'latest_posts' => $latest_posts,
        'pinned_announcement' => $pinned_announcement,
        'total_alumni' => $total_alumni,
        'alumni_data' => $alumni_data,
        'alumni_stats' => $alumni_stats,
        'events' => $events,
    ]);
})->name('home');

Route::get('/berita', [PublicPostController::class, 'index'])->name('portal.posts.index');
Route::get('/berita/{slug}', [PublicPostController::class, 'show'])->name('portal.posts.show');
Route::post('/berita/{slug}/comments', [PublicPostController::class, 'storeComment'])->name('portal.posts.comments.store')->middleware('throttle:3,1');
Route::get('/halaman/{slug}', [PublicPageController::class, 'show'])->name('portal.pages.show');
Route::get('/dokumen', [PublicDocumentController::class, 'index'])->name('portal.documents.index');
Route::get('/dokumen/download/{document}', [PublicDocumentController::class, 'download'])->name('portal.documents.download');
Route::get('/event', [PublicEventController::class, 'index'])->name('portal.events.index');
Route::get('/event/{slug}', [PublicEventController::class, 'show'])->name('portal.events.show');

// Sitemap Routes
Route::get('/sitemap.xml', [PublicPageController::class, 'sitemapXml'])->name('portal.sitemap.xml');
Route::get('/sitemap', [PublicPageController::class, 'sitemapHtml'])->name('portal.sitemap');

Route::get('/privacy-policy', function () {
    return Inertia::render('Public/PrivacyPolicy');
})->name('privacy-policy');
Route::get('/terms-of-service', function () {
    return Inertia::render('Public/TermsConditions');
})->name('terms-service');
Route::get('/cookie-policy', function () {
    return Inertia::render('Public/CookiePolicy');
})->name('cookie-policy');

// API: Unique check for registration (throttled)
// SEC-FIX: Returns GENERIC response — does not confirm whether email/NIM exists.
// This prevents email/NIM enumeration attacks.
Route::post('/api/check-user-exists', function (Request $request) {
    $request->validate([
        'email' => ['nullable', 'email', 'max:255'],
        'nomor_induk' => ['nullable', 'string', 'max:50'],
    ]);

    // ⚠️ SECURITY: Always return same structure regardless of existence.
    // Returning true/false directly would allow enumeration.
    // The frontend can use this for basic format validation only.
    return response()->json([
        'email_exists' => false, // Always false — form-level check disabled for security
        'nomor_induk_exists' => false,
        '_note' => 'Server-side uniqueness is enforced at submission.',
    ]);
})->middleware('throttle:1,1');

// ─── Authenticated Routes ────────────────────────────────────────────────────

// ─── Case A: Admin-Driven Account Activation (Public — no auth) ─────────────
// Users with pre-existing identities (mahasiswa, dosen, staff) claim their account
// by verifying NIM/NIDN + tanggal lahir → OTP → password creation.
Route::middleware(['guest'])->prefix('activate')->name('activation.')->group(function () {
    Route::get('/', [ActivationController::class, 'showIdentityForm'])->name('show');
    Route::post('/', [ActivationController::class, 'verifyIdentity'])->middleware('throttle:10,5');
    Route::get('/verify-otp', [ActivationController::class, 'showOtpForm'])->name('verify-otp');
    Route::post('/verify-otp', [ActivationController::class, 'verifyOtp'])->middleware('throttle:5,1');
    Route::post('/resend-otp', [ActivationController::class, 'resendOtp'])->middleware('throttle:3,5')->name('resend-otp');
    Route::get('/set-password', [ActivationController::class, 'showPasswordForm'])->name('set-password');
    Route::post('/set-password', [ActivationController::class, 'setPassword'])->middleware('throttle:5,1');
});

// ─── Case B: Self-Registration Activation (Signed URL — no auth) ─────────────
// After admin approves a RegistrationRequest, user receives a signed email link.
// Clicking the link → validates token → password creation → activated.
Route::get('/activate/confirm', [ActivationConfirmController::class, 'confirm'])
    ->middleware('signed')
    ->name('activation.confirm');
Route::get('/activate/confirm/otp', [ActivationConfirmController::class, 'showOtpForm'])->name('activation.confirm.otp');
Route::post('/activate/confirm/otp', [ActivationConfirmController::class, 'verifyOtp'])->middleware('throttle:5,1');
Route::post('/activate/confirm/resend-otp', [ActivationConfirmController::class, 'resendOtp'])->middleware('throttle:3,5')->name('activation.confirm.resend-otp');
Route::get('/activate/complete', [ActivationConfirmController::class, 'showCompleteForm'])->name('activation.complete');
Route::post('/activate/complete', [ActivationConfirmController::class, 'complete'])->middleware('throttle:5,1');

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

    // Waiting Room routes (accessed by pending/rejected users)
    Route::get('/waiting-room', [FirstTimeLoginController::class, 'showWaitingRoom'])->name('waiting-room');
    Route::get('/api/check-approval-status', [FirstTimeLoginController::class, 'checkApprovalStatus'])->name('approval.status');
    Route::post('/waiting-room/resign', [FirstTimeLoginController::class, 'resign'])->name('waiting-room.resign');

    // Core Application (post-OTP verification)
    Route::middleware([EnsureFirstTimeLoginComplete::class])->group(function () {
        Route::get('/dashboard', [PortalController::class, 'index'])->name('dashboard');
        Route::post('/select-module', [PortalController::class, 'selectModule'])->name('module.select');
        Route::post('/portal/switch-role', [PortalController::class, 'switchRole'])->name('portal.switch-role');
        Route::inertia('/portal', 'Portal')->name('portal');

        // PWA Subscription Routes
        Route::post('/pwa/subscribe', [PwaController::class, 'subscribe'])->name('pwa.subscribe');
        Route::post('/pwa/send-test', [PwaController::class, 'sendTest'])->name('pwa.send-test');
    });
});

// ─── Module Routes ───────────────────────────────────────────────────────────

require __DIR__.'/pagi.php';
require __DIR__.'/wims.php';
require __DIR__.'/fast.php';
require __DIR__.'/qr_verification.php';
require __DIR__.'/trace.php';

// ─── Portal Admin ────────────────────────────────────────────────────────────

Route::middleware(['auth', CheckRole::class.':super-admin'])
    ->prefix('portal-admin')
    ->name('portal-admin.')
    ->group(function () {
        Route::get('/', [PortalAdminController::class, 'index'])->name('dashboard');
        Route::get('instant-search', [PortalAdminController::class, 'instantSearch'])->name('instant-search');
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
        Route::post('documents/{document}/toggle-pin', [PortalDocumentController::class, 'togglePin'])->name('documents.toggle-pin');
        Route::resource('documents', PortalDocumentController::class);
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
            // BUG-015: Use 'super-admin' (hyphen) consistently — do not use 'super_admin' (underscore)
            ['name' => 'Muchlisin Maruf', 'password' => Hash::make('admin123'), 'user_type' => 'super-admin', 'email_verified_at' => now(), 'password_changed_at' => now()]
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
Route::post('/invitations/accept/{token}', [InvitationAcceptController::class, 'accept'])
    ->name('invitations.accept.post');

// ─── Image Proxy Obfuscated Streaming ──────────────────────────────────────────
Route::get('/images/v1/{encrypted_path}', [ImageProxyController::class, 'serve'])
    ->middleware('signed')
    ->name('images.proxy');

// ─── SEC-FIX: /dev-login-temp guarded to local environment only ──────────────
if (app()->isLocal()) {
    Route::get('/dev-login-temp', function () {
        $user = User::where('email', '=', 'muchlisinmaruf@gmail.com', 'and')->first();
        if (! $user) {
            abort(404, 'Dev user not found.');
        }
        Auth::login($user);

        return redirect('/portal-admin/appearance');
    })->name('dev.login.temp');
}

require __DIR__.'/settings.php';
