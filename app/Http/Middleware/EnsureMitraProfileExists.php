<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMitraProfileExists
{
    /**
     * Handle an incoming request.
     * Redirect to profile setup if the mitra profile doesn't exist.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->mitraProfile) {
            return redirect()->route('module.trace.open-job.mitra-profile-setup');
        }

        return $next($request);
    }
}
