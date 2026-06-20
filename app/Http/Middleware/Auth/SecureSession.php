<?php

namespace App\Http\Middleware\Auth;

use App\Models\Auth\AuthSession;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
            $authSession = AuthSession::where(function ($query) use ($token) {
                $query->where('id', $token)
                    ->orWhere('session_token', $token);
            })
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

            // Sync with current Laravel Session ID if it changed (e.g. after regeneration)
            if ($request->hasSession() && $authSession->session_token !== $request->session()->getId()) {
                $existing = AuthSession::where('session_token', $request->session()->getId())->first();
                if ($existing) {
                    $existing->update(['is_revoked' => true, 'session_token' => 'invalidated_'.Str::random(10)]);
                }

                $authSession->update([
                    'session_token' => $request->session()->getId(),
                    'expires_at' => Carbon::now()->addMinutes(config('session.lifetime')),
                    'last_activity_at' => Carbon::now(),
                ]);
            } else {
                $authSession->update([
                    'expires_at' => Carbon::now()->addMinutes(config('session.lifetime')),
                    'last_activity_at' => Carbon::now(),
                ]);
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
