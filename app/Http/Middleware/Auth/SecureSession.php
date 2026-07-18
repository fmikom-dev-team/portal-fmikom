<?php

namespace App\Http\Middleware\Auth;

use App\Models\Auth\AuthSession;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * SecureSession Middleware
 *
 * Validates the enterprise auth session on each request:
 *  1. Checks that the stored auth_session_token is valid and not revoked
 *  2. Checks idle timeout (server-side, immune to browser "restore session" bypass)
 *  3. Checks absolute session lifetime (max 8 hours regardless of activity)
 *  4. Syncs session_token after Laravel session ID regeneration
 *
 * FIX CRIT-01: Added server-side idle + absolute timeout tracking so that
 *              closing and restoring the browser cannot extend the session lifetime.
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
            // Cache AuthSession lookup per-token (30s) to avoid a DB hit on every request.
            // Cache is intentionally short-lived so revocations propagate within 30 seconds.
            $cacheKey = 'auth_sess_'.$token;
            $authSession = Cache::remember($cacheKey, 30, fn () => AuthSession::where(function ($query) use ($token) {
                $query->where('id', $token)
                    ->orWhere('session_token', $token);
            })
                ->where('user_id', $request->user()->id)
                ->first());

            if (! $authSession) {
                Cache::forget($cacheKey);

                return $this->reject($request, 'Session not found.');
            }

            if ($authSession->is_revoked) {
                Cache::forget($cacheKey);

                return $this->reject($request, 'Session has been revoked.');
            }

            if ($authSession->expires_at && Carbon::now()->isAfter($authSession->expires_at)) {
                $authSession->update(['is_revoked' => true]);
                Cache::forget($cacheKey);

                return $this->reject($request, 'Session expired.');
            }

            // ── [FIX CRIT-01 / MED-04] Absolute Session Timeout ─────────────────
            // A session may NEVER exceed this hard limit, even if the user is active.
            // Default: 8 hours. Set session.absolute_timeout_hours in config to override.
            // This prevents the browser "restore previous session" bypass where the
            // browser re-presents an old (supposedly expired) session cookie.
            $absoluteTimeoutHours = (int) config('session.absolute_timeout_hours', 8);
            if ($authSession->created_at) {
                $sessionAgeHours = Carbon::now()->diffInHours($authSession->created_at);
                if ($sessionAgeHours >= $absoluteTimeoutHours) {
                    $authSession->update(['is_revoked' => true]);
                    Cache::forget($cacheKey);

                    return $this->reject($request, "Session absolute timeout ({$absoluteTimeoutHours}h) exceeded. Please login again.");
                }
            }

            // ── [FIX CRIT-01] Server-Side Idle Timeout ───────────────────────────
            // Uses last_activity_at from the AuthSession DB record — NOT the Redis TTL.
            // This is the key fix for the "browser restore" bypass:
            //   - Browser restores session cookie → cookie still valid → Redis data still exists
            //   - But last_activity_at is past the idle window → server rejects the session
            // Idle timeout uses session.lifetime (minutes) from config.
            $idleTimeoutMinutes = (int) config('session.lifetime', 30);
            if ($authSession->last_activity_at) {
                $idleSinceMinutes = Carbon::now()->diffInMinutes($authSession->last_activity_at);
                if ($idleSinceMinutes >= $idleTimeoutMinutes) {
                    $authSession->update(['is_revoked' => true]);
                    Cache::forget($cacheKey);

                    return $this->reject($request, "Session idle timeout ({$idleTimeoutMinutes}m) exceeded.");
                }
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
                Cache::forget($cacheKey);
            } else {
                $authSession->update([
                    'expires_at' => Carbon::now()->addMinutes(config('session.lifetime')),
                    'last_activity_at' => Carbon::now(),
                ]);
            }

            // Use request attributes (internal bag) instead of merge() to prevent
            // the risk score from leaking into URLs, browser history, and Referer headers.
            $request->attributes->set('_session_risk_score', $authSession->risk_score);
        }

        return $next($request);
    }

    protected function reject(Request $request, string $reason): mixed
    {
        // Perform full server-side logout to invalidate all session data.
        $request->session()->forget('auth_session_token');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Session invalid: '.$reason], 401);
        }

        return redirect()->route('login')->with('error', $reason);
    }
}
