<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

if (!defined('ROLE_SUPER_ADMIN_SUFFIX')) {
    define('ROLE_SUPER_ADMIN_SUFFIX', ':super-admin');
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            /*
            |------------------------------------------------------------------
            | Enterprise Modular Route Loading
            |------------------------------------------------------------------
            | Routes are loaded in specific order:
            |   1. Public auth routes (no middleware) — OAuth, passkeys, SSO ACS
            |   2. Protected auth routes — sessions, MFA management
            |   3. WorkOS admin routes — all behind auth + super-admin
            */

            // ──────────────────────────────────────────────────────────────
            // Auth Routes — Public (no auth middleware)
            // These MUST stay outside any auth guard
            // ──────────────────────────────────────────────────────────────
            Route::middleware('web')
                ->group(base_path('routes/auth/oauth.php'));

            Route::middleware('web')
                ->group(base_path('routes/auth/magic-links.php'));

            Route::middleware('web')
                ->group(base_path('routes/auth/sso.php'));

            Route::middleware('web')
                ->group(base_path('routes/auth/passkeys.php'));

            // ──────────────────────────────────────────────────────────────
            // Auth Routes — Protected (require authentication)
            // ──────────────────────────────────────────────────────────────
            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/auth/sessions.php'));

            Route::middleware(['web', 'auth'])
                ->group(base_path('routes/auth/mfa.php'));

            // ──────────────────────────────────────────────────────────────
            // Auth Routes — Admin Only
            // ──────────────────────────────────────────────────────────────
            Route::middleware(['web', 'auth', \App\Http\Middleware\CheckRole::class . ROLE_SUPER_ADMIN_SUFFIX])
                ->group(base_path('routes/auth/password-policies.php'));

            Route::middleware(['web', 'auth', \App\Http\Middleware\CheckRole::class . ROLE_SUPER_ADMIN_SUFFIX])
                ->group(base_path('routes/auth/audit.php'));

            // ──────────────────────────────────────────────────────────────
            // WorkOS Admin Routes — Fully Protected
            // All WorkOS routes require: auth + super-admin role + device fingerprint
            // ──────────────────────────────────────────────────────────────
            $workosMiddleware = [
                'web',
                'auth',
                \App\Http\Middleware\CheckRole::class . ROLE_SUPER_ADMIN_SUFFIX,
                'device.fingerprint',
            ];

            // Catch-all Dashboard route moved to the bottom

            Route::middleware($workosMiddleware)
                ->prefix('workos')
                ->name('workos.')
                ->group(base_path('routes/workos/users.php'));

            Route::middleware($workosMiddleware)
                ->prefix('workos')
                ->name('workos.')
                ->group(base_path('routes/workos/roles.php'));

            Route::middleware($workosMiddleware)
                ->prefix('workos')
                ->name('workos.')
                ->group(base_path('routes/workos/permissions.php'));

            Route::middleware($workosMiddleware)
                ->prefix('workos')
                ->name('workos.')
                ->group(base_path('routes/workos/organizations.php'));

            Route::middleware($workosMiddleware)
                ->prefix('workos')
                ->name('workos.')
                ->group(base_path('routes/workos/radar.php'));

            Route::middleware($workosMiddleware)
                ->prefix('workos')
                ->name('workos.')
                ->group(base_path('routes/workos/audit.php'));

            Route::middleware($workosMiddleware)
                ->prefix('workos')
                ->name('workos.')
                ->group(base_path('routes/workos/auth-platform.php'));

            // ──────────────────────────────────────────────────────────────
            // WorkOS Dashboard Catch-all Route
            // MUST be registered last so it doesn't intercept API routes
            // ──────────────────────────────────────────────────────────────
            Route::middleware($workosMiddleware)
                ->prefix('workos')
                ->name('workos.')
                ->group(base_path('routes/workos/dashboard.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        // Global Middleware (Applies to all HTTP requests - Web, API, etc.)
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        // ──────────────────────────────────────────────────────────────────
        // Global Web Middleware Stack
        // ──────────────────────────────────────────────────────────────────
        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
        ]);

        // ──────────────────────────────────────────────────────────────────
        // Middleware Aliases (named shortcuts)
        // ──────────────────────────────────────────────────────────────────
        $middleware->alias([
            // Existing
            'module.context'   => \App\Http\Middleware\CheckActiveContext::class,
            'module.access'    => \App\Http\Middleware\EnsureModuleAccess::class,
            'role'             => \App\Http\Middleware\CheckRole::class,

            // Auth Platform — Enterprise Middleware
            'oauth.state'      => \App\Http\Middleware\Auth\OAuthStateValidation::class,
            'device.fingerprint' => \App\Http\Middleware\Auth\DeviceFingerprint::class,
            'secure.session'   => \App\Http\Middleware\Auth\SecureSession::class,
            'risk.score'       => \App\Http\Middleware\Auth\RiskScore::class,

            // Radar Security Shield — attach to auth routes to enable real detections
            'radar.shield'     => \App\Http\Middleware\Radar\RadarSecurityShield::class,
        ]);

        // ──────────────────────────────────────────────────────────────────
        // Rate Limiters — Configurable per feature
        // ──────────────────────────────────────────────────────────────────
        // Note: Define these in a ServiceProvider for cleaner separation,
        // but registering here works in Laravel 12's bootstrap-first pattern.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle CSRF token mismatch (session expired) for Inertia
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($e && $request->inertia()) {
                return response()->json([
                    'message' => 'Sesi Anda telah berakhir. Silakan muat ulang halaman.',
                ], 419);
            }
            return redirect()->route('login')
                ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        });
    })->create();
