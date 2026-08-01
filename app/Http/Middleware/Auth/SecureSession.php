<?php

namespace App\Http\Middleware\Auth;

use App\Models\Auth\AuthSession;
use App\Modules\WorkOs\Services\AuthPlatform\SessionEngine;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * SecureSession Middleware
 *
 * Validates the enterprise auth session on each request:
 *  1. Checks that the stored auth_session_token is valid and not revoked
 *  2. Checks idle timeout (server-side, immune to browser "restore session" bypass)
 *  3. Checks absolute session lifetime (max 8 hours regardless of activity)
 *  4. Refreshes last_activity_at + extends expires_at on each request (throttled)
 *
 * FIX CRIT-01: Added server-side idle + absolute timeout tracking so that
 *              closing and restoring the browser cannot extend the session lifetime.
 *
 * FIX CRIT-02: Removed Cache::lock() that caused Octane concurrent-request deadlock.
 *              Activity updates now use Cache::add() as a lightweight atomic flag.
 *
 * FIX CRIT-03: Session creation is now exclusively owned by each login controller /
 *              LoginService. The AppServiceProvider Login event listener no longer
 *              creates a duplicate session record.
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
            // ── Step 1: Resolve AuthSession record ───────────────────────────────
            // Cache per-token (30 s) so we avoid a DB hit on every single request.
            // Short TTL ensures revocations propagate within 30 seconds.
            $cacheKey = 'auth_sess_'.$token;
            $authSession = Cache::remember($cacheKey, 30, fn () => AuthSession::where('id', $token)
                ->where('user_id', $request->user()->id)
                ->first());

            // ── Step 2: Missing session record — graceful recovery ────────────────
            // Only possible if the DB row was manually deleted or the token was
            // written with a non-UUID value. Re-create rather than hard-logout.
            if (! $authSession) {
                Cache::forget($cacheKey);
                try {
                    $sessionEngine = app(SessionEngine::class);
                    $authSession = $sessionEngine->createSession($request->user(), $request);
                    $request->session()->put('auth_session_token', $authSession->id);
                } catch (\Throwable $e) {
                    return $this->reject($request, 'Session not found.');
                }
            }

            // ── Step 3: Revocation check ─────────────────────────────────────────
            // A revoked session is always a hard reject. No "un-revoke" recovery here
            // — if a session is revoked (admin action, logout, password reset), the
            // user must log in again. This is intentional security behaviour.
            if ($authSession->is_revoked) {
                Cache::forget($cacheKey);

                return $this->reject($request, 'Session has been revoked.');
            }

            // ── Step 4: Expiry check ─────────────────────────────────────────────
            if ($authSession->expires_at && Carbon::now()->isAfter($authSession->expires_at)) {
                $authSession->update(['is_revoked' => true]);
                Cache::forget($cacheKey);

                return $this->reject($request, 'Session expired.');
            }

            // ── Step 5: Absolute session timeout ─────────────────────────────────
            // A session may NEVER exceed this hard limit, even if the user is active.
            // Default: 8 hours. Prevents browser "restore previous session" bypass.
            $absoluteTimeoutHours = (int) config('session.absolute_timeout_hours', 8);
            if ($authSession->created_at) {
                $sessionAgeHours = Carbon::now()->diffInHours($authSession->created_at);
                if ($sessionAgeHours >= $absoluteTimeoutHours) {
                    $authSession->update(['is_revoked' => true]);
                    Cache::forget($cacheKey);

                    return $this->reject($request, "Session absolute timeout ({$absoluteTimeoutHours}h) exceeded. Please login again.");
                }
            }

            // ── Step 6: Idle timeout ─────────────────────────────────────────────
            // Uses last_activity_at from the AuthSession DB record — NOT the Redis TTL.
            // This is the key fix for the "browser restore" bypass:
            //   - Browser restores session cookie → cookie still valid → Redis data exists
            //   - But last_activity_at is past the idle window → server rejects the session
            $idleTimeoutMinutes = (int) config('session.lifetime', 30);
            if ($authSession->last_activity_at) {
                $idleSinceMinutes = Carbon::now()->diffInMinutes($authSession->last_activity_at);
                if ($idleSinceMinutes >= $idleTimeoutMinutes) {
                    $authSession->update(['is_revoked' => true]);
                    Cache::forget($cacheKey);

                    return $this->reject($request, "Session idle timeout ({$idleTimeoutMinutes}m) exceeded.");
                }
            }

            // ── Step 7: Throttled activity ping ──────────────────────────────────
            // Update last_activity_at + slide expires_at at most once every 10 seconds.
            // Using Cache::add() (atomic SET-IF-NOT-EXISTS) instead of a lock so that
            // concurrent Octane requests don't deadlock each other.
            $activityKey = 'sess_act_'.$authSession->id;
            if (Cache::add($activityKey, true, 10)) {
                $authSession->update([
                    'expires_at'       => Carbon::now()->addMinutes(config('session.lifetime')),
                    'last_activity_at' => Carbon::now(),
                ]);
                // Invalidate the cached copy so the fresh timestamps are picked up on
                // the next lookup (after the 10-second throttle window expires).
                Cache::forget($cacheKey);
            }

            // ── Step 8: Share risk score with downstream middleware/controllers ───
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
