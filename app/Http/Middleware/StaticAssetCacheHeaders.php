<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * StaticAssetCacheHeaders
 *
 * Menambahkan header Cache-Control yang tepat untuk aset statis yang
 * dilayani oleh Octane/FrankenPHP (tanpa Nginx di depannya).
 *
 * - Vite build assets (/build/assets/*): immutable, 1 tahun
 *   Aman karena setiap build memiliki hash unik di nama file.
 * - Font files (/fonts/*): immutable, 1 tahun
 *   Tidak pernah berubah kecuali font itu sendiri diganti.
 * - PWA assets (/asset/*): stale-while-revalidate, 7 hari
 *   Bisa berubah jika admin ganti logo, jadi beri sedikit fleksibilitas.
 *
 * Hasilnya: browser dan Cloudflare CDN akan menyimpan cache aset-aset ini,
 * sehingga kunjungan berikutnya sangat cepat (CSS/JS/font dari cache).
 */
class StaticAssetCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $path = $request->getPathInfo();

        // Vite build assets: hash di nama file → immutable, 1 tahun
        if (str_starts_with($path, '/build/assets/')) {
            $response->headers->set(
                'Cache-Control',
                'public, max-age=31536000, immutable'
            );
            $response->headers->set('Vary', 'Accept-Encoding');
            return $response;
        }

        // Self-hosted fonts: tidak berubah kecuali sengaja diganti → immutable, 1 tahun
        if (str_starts_with($path, '/fonts/')) {
            $response->headers->set(
                'Cache-Control',
                'public, max-age=31536000, immutable'
            );
            $response->headers->set('Vary', 'Accept-Encoding');
            return $response;
        }

        // PWA & brand assets: bisa berubah tapi jarang → stale-while-revalidate 7 hari
        if (str_starts_with($path, '/asset/')) {
            $response->headers->set(
                'Cache-Control',
                'public, max-age=604800, stale-while-revalidate=86400'
            );
            $response->headers->set('Vary', 'Accept-Encoding');
            return $response;
        }

        // Manifest PWA: short TTL agar admin bisa update tanpa bingung cache
        if ($path === '/manifest.json') {
            $response->headers->set(
                'Cache-Control',
                'public, max-age=3600, stale-while-revalidate=86400'
            );
            return $response;
        }

        return $response;
    }
}
