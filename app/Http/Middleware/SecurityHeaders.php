<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

/**
 * Security Headers Middleware
 *
 * Melindungi dari:
 * - XSS (Content-Security-Policy, X-XSS-Protection)
 * - Clickjacking (X-Frame-Options, CSP frame-ancestors)
 * - MIME Sniffing (X-Content-Type-Options)
 * - Protocol Downgrade (Strict-Transport-Security)
 * - Information Disclosure (Referrer-Policy, Permissions-Policy)
 * - SSRF & resource injection (CSP connect-src, img-src)
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowsDocumentEmbedding = $this->allowsDocumentEmbedding($request);
        $telescopePath = config('telescope.path', 'telescope').'*';
        $pulsePath = config('pulse.path', 'pulse').'*';
        $horizonPath = config('horizon.path', 'horizon').'*';

        if ($request->is($telescopePath) || $request->is($pulsePath) || $request->is($horizonPath) || $request->is('livewire*')) {
            $response = $next($request);
            $response->headers->set('Content-Security-Policy', "default-src * 'unsafe-inline' 'unsafe-eval' data: blob:; connect-src * 'unsafe-inline' ws: wss:; img-src * data: blob:; style-src * 'unsafe-inline';");
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-XSS-Protection', '0');
            $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

            // Add modern security headers to dev/livewire tools for consistency
            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
            $response->headers->set('Cross-Origin-Embedder-Policy', 'unsafe-none');
            $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
            $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

            return $response;
        }

        // ── Nonce Generation ─────────────────────────────────────────────────
        $nonce = base64_encode(random_bytes(16));
        $request->attributes->set('csp-nonce', $nonce);
        view()->share('csp_nonce', $nonce);

        // Register the nonce with Laravel Vite if available
        if (class_exists(Vite::class)) {
            Vite::useCspNonce($nonce);
        }

        $response = $next($request);

        $isLocalEnvironment = config('app.debug') || app()->environment('local', 'testing');
        $localDevScriptOrigins = [
            'http://0.0.0.0:5173',
            'http://127.0.0.1:5173',
            'http://localhost:5173',
            'ws://0.0.0.0:5173',
            'ws://127.0.0.1:5173',
            'ws://localhost:5173',
        ];

        // blob: is required for FFmpeg WASM: the bundled Worker does import(blob://...) for ffmpeg-core.js
        // script-src-elem only covers <script> tags; dynamic import() in Workers uses script-src
        $scriptSrcParts = [
            "script-src 'self' 'nonce-{$nonce}' blob: https://static.cloudflareinsights.com https://cdnjs.cloudflare.com",
        ];

        if ($isLocalEnvironment) {
            $scriptSrcParts[] = implode(' ', $localDevScriptOrigins);
        }

        $scriptSrc = implode(' ', $scriptSrcParts);

        if ($isLocalEnvironment) {
            $scriptSrc .= " 'unsafe-eval'";
        }

        $host = $request->getHost();
        $isLocalHost = in_array($host, ['localhost', '127.0.0.1', '::1']);

        // Base connect-src URLs
        $connectSrcUrls = [
            "'self'",
            'https://openrouter.ai',
            'https://cdnjs.cloudflare.com',
            'https://generativelanguage.googleapis.com',
            'https://cloudflareinsights.com',
            'https://static.cloudflareinsights.com',
            'https://nominatim.openstreetmap.org',
            "ws://{$host}",
            "wss://{$host}",
        ];

        // Dynamically add Reverb configuration to connect-src
        $reverbHost = config('broadcasting.connections.reverb.options.host');
        $reverbPort = config('broadcasting.connections.reverb.options.port');
        $reverbScheme = config('broadcasting.connections.reverb.options.scheme') === 'https' ? 'wss' : 'ws';

        if ($reverbHost) {
            $reverbUrl = "{$reverbScheme}://{$reverbHost}";
            if ($reverbPort) {
                $reverbUrl .= ":{$reverbPort}";
            }
            $connectSrcUrls[] = $reverbUrl;

            // If local reverb host, allow variations to prevent cross-origin issues
            if ($reverbHost === 'localhost') {
                $connectSrcUrls[] = "{$reverbScheme}://127.0.0.1".($reverbPort ? ":{$reverbPort}" : '');
            } elseif ($reverbHost === '127.0.0.1') {
                $connectSrcUrls[] = "{$reverbScheme}://localhost".($reverbPort ? ":{$reverbPort}" : '');
            }
        }

        // Add Vite Reverb Host if defined (for separated frontend connection via tunnels)
        $viteReverbHost = env('VITE_REVERB_HOST');
        $viteReverbPort = env('VITE_REVERB_PORT');
        $viteReverbScheme = env('VITE_REVERB_SCHEME') === 'https' ? 'wss' : 'ws';
        if ($viteReverbHost) {
            $viteReverbUrl = "{$viteReverbScheme}://{$viteReverbHost}";
            if ($viteReverbPort && $viteReverbPort != 443 && $viteReverbPort != 80) {
                $viteReverbUrl .= ":{$viteReverbPort}";
            }
            $connectSrcUrls[] = $viteReverbUrl;
        }

        // Also allow local ws/wss for the request host with any port (for HMR, dev servers, etc.)
        if ($isLocalHost) {
            $connectSrcUrls[] = 'ws://localhost:*';
            $connectSrcUrls[] = 'wss://localhost:*';
            $connectSrcUrls[] = 'ws://127.0.0.1:*';
            $connectSrcUrls[] = 'wss://127.0.0.1:*';
        }

        if ($isLocalEnvironment) {
            $connectSrcUrls = array_merge($connectSrcUrls, $localDevScriptOrigins, [
                'http://0.0.0.0:5173',
                'http://127.0.0.1:5173',
                'http://localhost:5173',
            ]);
        }

        $connectSrc = 'connect-src '.implode(' ', array_unique($connectSrcUrls));

        $cspDirectives = [
            "default-src 'self'",
            $scriptSrc,
            // fonts.bunny.net digunakan oleh project (Instrument Sans), fonts.googleapis.com sebagai fallback
            'style-src '.implode(' ', array_filter([
                "'self'",
                "'unsafe-inline'",
                'https://fonts.googleapis.com',
                'https://fonts.bunny.net',
                $isLocalEnvironment ? 'http://0.0.0.0:5173 http://127.0.0.1:5173 http://localhost:5173' : null,
            ])),
            'style-src-elem '.implode(' ', array_filter([
                "'self'",
                $isLocalEnvironment ? "'unsafe-inline'" : "'nonce-{$nonce}'",
                'https://fonts.googleapis.com',
                'https://fonts.bunny.net',
                $isLocalEnvironment ? 'http://0.0.0.0:5173 http://127.0.0.1:5173 http://localhost:5173' : null,
            ])),
            "style-src-attr 'unsafe-inline'",
            "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net data:",
            "img-src 'self' data: blob: https://ui-avatars.com https://api.dicebear.com https://avatars.dicebear.com https://lh3.googleusercontent.com https://lh4.googleusercontent.com https://lh5.googleusercontent.com https://lh6.googleusercontent.com https://images.unsplash.com https://upload.wikimedia.org https://cdn.jsdelivr.net https://tile.openstreetmap.org https://a.tile.openstreetmap.org https://b.tile.openstreetmap.org https://c.tile.openstreetmap.org",
            $connectSrc,
            "media-src 'self' blob:",
            // worker-src: FFmpeg WASM creates a Web Worker from a bundled asset URL.
            // blob: is needed because the Worker internally does import(blob://...) to load ffmpeg-core.js
            "worker-src 'self' blob:",
            // script-src blob: is needed for the Worker's internal dynamic import(blob://...) of ffmpeg-core.js
            'script-src-elem '.implode(' ', array_filter([
                "'self'",
                $isLocalEnvironment ? "'unsafe-inline'" : "'nonce-{$nonce}'",
                'blob:',
                'https://static.cloudflareinsights.com',
                'https://cdnjs.cloudflare.com',
                $isLocalEnvironment ? 'http://0.0.0.0:5173 http://127.0.0.1:5173 http://localhost:5173' : null,
            ])),
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            $allowsDocumentEmbedding ? "frame-ancestors 'self'" : "frame-ancestors 'none'",
        ];

        // Support proxy / Cloudflare SSL termination
        $isSecure = $request->isSecure() || strtolower($request->header('X-Forwarded-Proto', '')) === 'https';

        if ($isSecure && ! $isLocalHost) {
            $cspDirectives[] = 'upgrade-insecure-requests';
        }

        $csp = implode('; ', $cspDirectives);
        $response->headers->set('Content-Security-Policy', $csp);

        // ── Anti-Clickjacking ────────────────────────────────────────────────
        // Ditetapkan ke DENY karena CSP frame-ancestors 'none' sudah menangani ini.
        // X-Frame-Options sebagai fallback untuk browser lama yang tidak support CSP.
        $response->headers->set('X-Frame-Options', $allowsDocumentEmbedding ? 'SAMEORIGIN' : 'DENY');

        // ── Anti MIME Sniffing ───────────────────────────────────────────────
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // ── Legacy XSS Filter (browser lama) ────────────────────────────────
        // Diatur ke 0 untuk menonaktifkan filter XSS bawaan karena filter bawaan dapat memperkenalkan kerentanan baru
        $response->headers->set('X-XSS-Protection', '0');

        // ── Paksa HTTPS (1 tahun) ────────────────────────────────────────────
        if ($isSecure && ! $isLocalHost) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // ── Batasi info referrer ke site eksternal ────────────────────────────
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // ── Batasi akses fitur browser berbahaya ──────────────────────────────
        $response->headers->set('Permissions-Policy', $this->buildPermissionsPolicy($request));

        // ── Cross-Origin Security Headers ────────────────────────────────────
        if ($isSecure) {
            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
            $response->headers->set('Cross-Origin-Embedder-Policy', 'unsafe-none');
            $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        }
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        // ── Hapus header yang mengidentifikasi teknologi server ───────────────
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        if (function_exists('header_remove')) {
            @header_remove('X-Powered-By');
        }

        return $response;
    }

    private function allowsDocumentEmbedding(Request $request): bool
    {
        return $request->is(
            'documents/surat/*',
            'documents/public/surat/*',
            'admin/surat/*',
        );
    }

    private function buildPermissionsPolicy(Request $request): string
    {
        $allowsWimsAttendanceSensors = $request->routeIs(
            'wims.attendance',
            'wims.absensi.store',
            'wims.absensi.checkout',
        );

        return implode(', ', [
            $allowsWimsAttendanceSensors ? 'camera=(self)' : 'camera=()',
            'microphone=()',
            $allowsWimsAttendanceSensors ? 'geolocation=(self)' : 'geolocation=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
        ]);
    }
}
