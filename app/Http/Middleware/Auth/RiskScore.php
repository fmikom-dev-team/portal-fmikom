<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use App\Models\Auth\AuthLoginAttempt;
use Carbon\Carbon;

/**
 * RiskScore Middleware
 *
 * Blocks requests from IP addresses with a very high risk score (brute force).
 * Can be attached to public auth endpoints.
 */
class RiskScore
{
    protected int $maxFailedAttempts = 10;
    protected int $windowMinutes = 15;

    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();

        $recentFailures = AuthLoginAttempt::where('ip_address', $ip)
            ->where('is_successful', false)
            ->where('created_at', '>=', Carbon::now()->subMinutes($this->windowMinutes))
            ->count();

        if ($recentFailures >= $this->maxFailedAttempts) {
            return response()->json([
                'error' => 'Too many failed attempts. Access temporarily blocked.',
                'retry_after_minutes' => $this->windowMinutes,
            ], 429);
        }

        return $next($request);
    }
}
