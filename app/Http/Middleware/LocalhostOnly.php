<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * LocalhostOnly Middleware
 *
 * [FIX HIGH-03] Restricts route access to requests originating from localhost only.
 * Applied to testing routes (__testing/*) to prevent access via Cloudflare Tunnel
 * or any external network, even when APP_ENV=local is deployed remotely.
 *
 * Blocks: all IPs except 127.0.0.1, ::1
 */
class LocalhostOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

        if (! in_array($remoteAddr, ['127.0.0.1', '::1'], true)) {
            abort(403, 'Testing routes are only accessible from localhost.');
        }

        return $next($request);
    }
}
