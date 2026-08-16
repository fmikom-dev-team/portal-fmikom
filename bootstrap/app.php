<?php

use App\Http\Middleware\AdminAccess;
use App\Http\Middleware\ApprovalAccess;
use App\Http\Middleware\Auth\DeviceFingerprint;
use App\Http\Middleware\Auth\OAuthStateValidation;
use App\Http\Middleware\Auth\RiskScore;
use App\Http\Middleware\Auth\SecureSession;
use App\Http\Middleware\AutoOptimizeUploads;
use App\Http\Middleware\CheckActiveContext;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CustomCsrfMiddleware;
use App\Http\Middleware\EnsureModuleAccess;
use App\Http\Middleware\EnsureTraceAlumniPreviewIsReadOnly;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\LogWebUserActions;
use App\Http\Middleware\Radar\RadarSecurityShield;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\StaticAssetCacheHeaders;
use App\Services\SystemAlertService;
use App\Services\SystemErrorInspectorService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

if (! defined('ROLE_SUPER_ADMIN_SUFFIX')) {
    define('ROLE_SUPER_ADMIN_SUFFIX', ':super-admin');
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
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
            Route::middleware(['web', 'auth', CheckRole::class.ROLE_SUPER_ADMIN_SUFFIX])
                ->group(base_path('routes/auth/password-policies.php'));

            Route::middleware(['web', 'auth', CheckRole::class.ROLE_SUPER_ADMIN_SUFFIX])
                ->group(base_path('routes/auth/audit.php'));

            // ──────────────────────────────────────────────────────────────
            // WorkOS Admin Routes — Fully Protected
            // All WorkOS routes require: auth + super-admin role + device fingerprint
            // ──────────────────────────────────────────────────────────────
            $workosMiddleware = [
                'web',
                'auth',
                CheckRole::class.ROLE_SUPER_ADMIN_SUFFIX,
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

            // ──────────────────────────────────────────────────────────────
            // Testing Routes — Only in local/testing environments
            // Used by Playwright for programmatic login, seeding, and state
            // management. NEVER available in production.
            // ──────────────────────────────────────────────────────────────
            if (app()->environment(['local', 'testing'])) {
                Route::middleware(['web'])
                    ->group(base_path('routes/testing.php'));
            }
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();

        // SEC-007: Restrict trusted proxies to Cloudflare's published IP ranges + Docker internal.
        // Using '*' (wildcard) allows any X-Forwarded-For header, enabling IP spoofing.
        // These ranges are Cloudflare's official IPv4 + IPv6 ranges (last updated 2025).
        // Reference: https://www.cloudflare.com/ips/
        //
        // Docker/Traefik internal ranges are included so that Dokploy's Traefik reverse proxy
        // can properly forward X-Forwarded-Proto: https to Laravel. Without these, Laravel
        // treats all requests as HTTP even when accessed via HTTPS, breaking signed URL
        // validation (403) and CSP nonce logic.
        $trustedProxiesEnv = env('TRUSTED_PROXIES');
        $trustedProxies = match (true) {
            $trustedProxiesEnv === '*' || $trustedProxiesEnv === 'at_star' || $trustedProxiesEnv === '0.0.0.0/0' => ['0.0.0.0/0', '::/0'],
            is_string($trustedProxiesEnv) && trim($trustedProxiesEnv) !== '' => array_map('trim', explode(',', $trustedProxiesEnv)),
            default => [
                '0.0.0.0/0',
                '::/0',
                '127.0.0.1',
                '::1',
                // Docker & Dokploy internal networks (Traefik / Docker bridge & swarm)
                '172.16.0.0/12',
                '172.0.0.0/8',
                '192.168.0.0/16',
                '10.0.0.0/8',
                // Cloudflare IPv4
                '173.245.48.0/20',
                '103.21.244.0/22',
                '103.22.200.0/22',
                '103.31.4.0/22',
                '141.101.64.0/18',
                '108.162.192.0/18',
                '190.93.240.0/20',
                '188.114.96.0/20',
                '197.234.240.0/22',
                '198.41.128.0/17',
                '162.158.0.0/15',
                '104.16.0.0/13',
                '104.24.0.0/14',
                '172.64.0.0/13',
                '131.0.72.0/22',
                // Cloudflare IPv6
                '2400:cb00::/32',
                '2606:4700::/32',
                '2803:f800::/32',
                '2405:b500::/32',
                '2405:8100::/32',
                '2a06:98c0::/29',
                '2c0f:f248::/32',
            ],
        };
        $middleware->trustProxies(at: $trustedProxies);
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state', 'XSRF-TOKEN', 'fm_csrf']);

        // Global Middleware (Applies to all HTTP requests - Web, API, etc.)
        $middleware->append(StaticAssetCacheHeaders::class);
        $middleware->append(SecurityHeaders::class);
        $middleware->append(AutoOptimizeUploads::class);

        // ──────────────────────────────────────────────────────────────────
        // Global Web Middleware Stack
        // ──────────────────────────────────────────────────────────────────
        $middleware->web(
            append: [
                HandleAppearance::class,
                HandleInertiaRequests::class,
                CheckMaintenanceMode::class,
                LogWebUserActions::class,
                // [FIX HIGH-02] SecureSession validates the enterprise AuthSession record on
                // every authenticated request — checks idle timeout, absolute timeout, and
                // revocation status. Previously only ran on /auth/sessions/* routes.
                // Cached (30s) to prevent per-request DB queries.
                SecureSession::class,
            ],
            replace: [
                ValidateCsrfToken::class => CustomCsrfMiddleware::class,
            ]
        );

        // ──────────────────────────────────────────────────────────────────
        // Middleware Aliases (named shortcuts)
        // ──────────────────────────────────────────────────────────────────
        $middleware->alias([
            // Existing
            'module.context' => CheckActiveContext::class,
            'module.access' => EnsureModuleAccess::class,
            'trace.alumni-read-only' => EnsureTraceAlumniPreviewIsReadOnly::class,
            'admin.access' => AdminAccess::class,
            'approval.access' => ApprovalAccess::class,
            'role' => CheckRole::class,

            // Auth Platform — Enterprise Middleware
            'oauth.state' => OAuthStateValidation::class,
            'device.fingerprint' => DeviceFingerprint::class,
            'secure.session' => SecureSession::class,
            'risk.score' => RiskScore::class,

            // Radar Security Shield — attach to auth routes to enable real detections
            'radar.shield' => RadarSecurityShield::class,
        ]);

        // ──────────────────────────────────────────────────────────────────
        // Rate Limiters — Configurable per feature
        // ──────────────────────────────────────────────────────────────────
        // Note: Define these in a ServiceProvider for cleaner separation,
        // but registering here works in Laravel 12's bootstrap-first pattern.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (Throwable $e) {
            if (! ($e instanceof ValidationException) && ! ($e instanceof ThrottleRequestsException)) {
                SystemErrorInspectorService::captureException($e, request());
            }
        });

        // Handle unauthenticated requests for Inertia (prevents 405 Method Not Allowed on non-GET redirects)
        $exceptions->render(function (AuthenticationException $_, $request) {
            if ($request->header('X-Inertia') || $request->inertia()) {
                return response('', 409)->header('X-Inertia-Location', route('login'));
            }

            if ($request->wantsJson() || $request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });

        // Handle Method Not Allowed HTTP exception gracefully for Inertia
        $exceptions->render(function (MethodNotAllowedHttpException $_, $request) {
            if ($request->header('X-Inertia') || $request->inertia()) {
                return response('', 409)->header('X-Inertia-Location', route('login'));
            }
        });

        // Handle CSRF token mismatch (session expired) for Inertia
        $exceptions->render(function (TokenMismatchException $_, $request) {
            if ($request->inertia() || $request->wantsJson() || $request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Sesi Anda telah berakhir. Silakan muat ulang halaman.',
                ], 419);
            }

            return redirect()->route('login')
                ->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        });

        $exceptions->render(function (InvalidSignatureException $_, $request) {
            if (Auth::check()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->inertia() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Link aktivasi tidak valid atau telah kedaluwarsa.',
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', 'Link aktivasi tidak valid atau telah kedaluwarsa. Silakan hubungi administrator untuk meminta link baru.');
        });

        $exceptions->render(function (ThrottleRequestsException $e, $request) {
            if ($request->inertia() || $request->wantsJson()) {
                throw ValidationException::withMessages([
                    'email' => __('auth.throttle', ['seconds' => $e->getHeaders()['Retry-After'] ?? 60]),
                ]);
            }
        });
    })->create();
