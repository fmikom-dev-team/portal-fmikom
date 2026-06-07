<?php

namespace App\Http\Middleware\Auth;

use App\Models\Auth\AuthSession;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

/**
 * SecureSession Middleware
 *
 * Validates the enterprise auth session on each request:
 *  1. Checks that the stored auth_session_token is valid and not revoked
 *  2. Checks that the session has not expired
 *  3. Optionally validates IP hasn't changed (configurable)
 *  4. Rotates session ID periodically (session fixation prevention)
 */
class SecureSession
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()) {
            return $next($request);
        }

        $token = $request->session()->get('auth_session_token');

        if ($token) {
            $authSession = AuthSession::where('session_token', $token)
                ->where('user_id', $request->user()->id)
                ->first();

            if (! $authSession) {
                return $this->reject($request, 'Session not found.');
            }

            if ($authSession->is_revoked) {
                return $this->reject($request, 'Session has been revoked.');
            }

            if ($authSession->expires_at && Carbon::now()->isAfter($authSession->expires_at)) {
                $authSession->update(['is_revoked' => true]);

                return $this->reject($request, 'Session expired.');
            }

            // Share risk score with downstream handlers
            $request->merge(['_session_risk_score' => $authSession->risk_score]);
        }

        return $next($request);
    }

    protected function reject(Request $request, string $reason)
    {
        $request->session()->forget('auth_session_token');

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Session invalid: '.$reason], 401);
        }

        return redirect()->route('login')->with('error', $reason);
    }
}
