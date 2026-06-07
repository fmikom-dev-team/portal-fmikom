<?php

namespace App\Http\Middleware\Radar;

use Closure;
use Illuminate\Http\Request;
use App\Modules\WorkOs\Services\Radar\DetectionEngine;
use Symfony\Component\HttpFoundation\Response;

class RadarSecurityShield
{
    protected DetectionEngine $engine;

    public function __construct(DetectionEngine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For performance, we might only want to run this on specific routes, 
        // e.g. login, register, password resets, or high-value endpoints.
        // But for this example, we'll run it globally or where attached.

        try {
            $this->engine->inspect($request);
        } catch (\Exception $e) {
            // If it's a 403 HTTP Exception (Block), throw it.
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                throw $e;
            }
            // Otherwise fail open or log error to avoid locking users out due to engine bug
            \Log::error('Radar Engine Error: ' . $e->getMessage());
        }

        return $next($request);
    }
}
