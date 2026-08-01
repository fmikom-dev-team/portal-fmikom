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
 * ALL date comparisons use UNIX Timestamps (epoch seconds) to ensure 100% immunity
 * to application/database timezone mismatches (e.g. UTC vs Asia/Jakarta).
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
            if ($authSession->is_revoked) {
                Cache::forget($cacheKey);

                return $this->reject($request, 'Session has been revoked.');
            }

            $now = Carbon::now();
            $nowTimestamp = $now->getTimestamp();

            // ── Step 4: Expiry check (epoch seconds comparison) ───────────────────
            if ($authSession->expires_at && $nowTimestamp > $authSession->expires_at->getTimestamp()) {
                $authSession->update(['is_revoked' => true]);
                Cache::forget($cacheKey);

                return $this->reject($request, 'Session expired.');
            }

            // ── Step 5: Absolute session timeout (epoch seconds comparison) ──────
            // Default: 8 hours. Prevents browser "restore previous session" bypass.
            $absoluteTimeoutHours = (int) config('session.absolute_timeout_hours', 8);
            if ($authSession->created_at) {
                $sessionAgeSeconds = $nowTimestamp - $authSession->created_at->getTimestamp();
                if ($sessionAgeSeconds >= ($absoluteTimeoutHours * 3600)) {
                    $authSession->update(['is_revoked' => true]);
                    Cache::forget($cacheKey);

                    return $this->reject($request, "Session absolute timeout ({$absoluteTimeoutHours}h) exceeded. Please login again.");
                }
            }

            // ── Step 6: Server-side idle timeout (epoch seconds comparison) ─────
            // Uses last_activity_at epoch seconds vs current epoch seconds.
            // 100% immune to timezone offsets (UTC vs Asia/Jakarta).
            $idleTimeoutMinutes = (int) config('session.lifetime', 30);
            if ($authSession->last_activity_at) {
                $idleSinceSeconds = $nowTimestamp - $authSession->last_activity_at->getTimestamp();
                if ($idleSinceSeconds >= ($idleTimeoutMinutes * 60)) {
                    $authSession->update(['is_revoked' => true]);
                    Cache::forget($cacheKey);

                    return $this->reject($request, "Session idle timeout ({$idleTimeoutMinutes}m) exceeded.");
                }
            }

            // ── Step 7: Throttled activity ping ──────────────────────────────────
            // Update last_activity_at + slide expires_at at most once every 10 seconds.
            $activityKey = 'sess_act_'.$authSession->id;
            if (Cache::add($activityKey, true, 10)) {
                $authSession->update([
                    'expires_at' => $now->copy()->addMinutes(config('session.lifetime')),
                    'last_activity_at' => $now,
                ]);
                Cache::forget($cacheKey);
            }

            // ── Step 8: Share risk score with downstream middleware/controllers ───
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
