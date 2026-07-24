<?php

use App\Enums\UserAccountStatus;
use App\Http\Middleware\LocalhostOnly;
use App\Models\User;
use Database\Seeders\PlaywrightSeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

/**
 * Testing Routes — Hanya tersedia di environment testing/local.
 * Routes ini dipakai oleh Playwright untuk:
 *   - Login programmatic (bypass CSRF untuk storage state)
 *   - Seed test data
 *   - Reset state setelah/sebelum test
 *
 * SECURITY: Routes ini TIDAK pernah di-register di production.
 *
 * [FIX HIGH-03] Tambahan: IP whitelist — hanya localhost yang boleh akses.
 * Mencegah akses melalui Cloudflare Tunnel atau network eksternal bahkan jika
 * APP_ENV=local di-deploy ke server remote.
 */
Route::prefix('__testing')
    ->name('testing.')
    ->middleware([LocalhostOnly::class])
    ->group(function () {

        /**
         * GET /__testing/ping
         * Memastikan server testing berjalan.
         */
        Route::get('/ping', fn () => response()->json([
            'status' => 'ok',
            'env' => app()->environment(),
            'time' => now()->toIso8601String(),
        ]));

        /**
         * POST /__testing/seed
         * Menjalankan PlaywrightSeeder untuk membuat test users.
         */
        Route::post('/seed', function () {
            app(PlaywrightSeeder::class)->run();

            return response()->json([
                'status' => 'seeded',
                'users' => array_map(
                    fn ($u) => ['email' => $u['email'], 'type' => $u['type']],
                    PlaywrightSeeder::USERS
                ),
            ]);
        });

        /**
         * POST /__testing/login
         * Login sebagai user berdasarkan email.
         * Playwright menggunakan ini untuk setup storage state (saved auth).
         *
         * Body: { "email": "playwright.superadmin@fmikom.test" }
         */
        Route::post('/login', function (Request $request) {
            $request->validate([
                'email' => ['required', 'email'],
            ]);

            $user = User::where('email', $request->email)->first();

            if (! $user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            Auth::login($user, false);
            $request->session()->regenerate();

            return response()->json([
                'status' => 'logged_in',
                'user_id' => $user->id,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'name' => $user->name,
            ]);
        });

        /**
         * POST /__testing/logout
         * Logout dari session saat ini.
         */
        Route::post('/logout', function (Request $request) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json(['status' => 'logged_out']);
        });

        /**
         * GET /__testing/me
         * Mengembalikan info user yang sedang login (untuk verifikasi storage state).
         */
        Route::get('/me', function (Request $request) {
            if (! Auth::check()) {
                return response()->json(['authenticated' => false], 401);
            }

            $user = Auth::user();

            return response()->json([
                'authenticated' => true,
                'user_id' => $user->id,
                'email' => $user->email,
                'user_type' => $user->user_type,
                'name' => $user->name,
                'status' => $user->status_approval,
            ]);
        });

        /**
         * POST /__testing/create-user
         * Membuat user baru dengan data custom untuk test spesifik.
         */
        Route::post('/create-user', function (Request $request) {
            $data = $request->validate([
                'name' => ['required', 'string'],
                'email' => ['required', 'email'],
                'password' => ['required', 'string'],
                'user_type' => ['required', 'string'],
                'status_approval' => ['nullable', 'string'],
            ]);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'user_type' => $data['user_type'],
                    'status_approval' => UserAccountStatus::from($data['status_approval'] ?? 'activated'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'password_changed_at' => now(),
                ]
            );

            return response()->json([
                'status' => 'created',
                'user_id' => $user->id,
                'email' => $user->email,
            ], 201);
        });
    });
