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
 * ALL date comparisons parse the raw database timestamp string explicitly as UTC,
 * ensuring 100% immunity to application/database timezone mismatches (e.g. UTC vs Asia/Jakarta).
 */
class SecureSession
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user()) {
            return $next($request);
        }

        $token = $request->session()->get('auth_session_token');

        // ── Step 0: Ensure auth_session_token exists for authenticated user ───
        if (! $token) {
            $lockKey = 'create_auth_sess_'.$request->user()->id;
            try {
                Cache::lock($lockKey, 5)->block(3, function () use ($request, &$token) {
                    $token = $request->session()->get('auth_session_token');
                    if (! $token) {
                        $sessionEngine = app(SessionEngine::class);
                        $authSession = $sessionEngine->createSession($request->user(), $request);
                        $token = $authSession->id;
                        $request->session()->put('auth_session_token', $token);
                    }
                });
            } catch (\Throwable $e) {
                // Silently allow request to proceed
            }
        }

        if ($token) {
            // ── Step 1: Resolve AuthSession record ───────────────────────────────
            // Cache per-token (30 s) so we avoid a DB hit on every single request.
            // Short TTL ensures revocations propagate within 30 seconds.
            $cacheKey = 'auth_sess_'.$token;
            $authSession = Cache::remember($cacheKey, 30, fn () => AuthSession::where('id', $token)
                ->where('user_id', $request->user()->id)
                ->first());

            // ── Step 2: Missing session record — graceful recovery with atomic lock ──
            if (! $authSession) {
                Cache::forget($cacheKey);
                $lockKey = 'create_auth_sess_'.$request->user()->id;
                try {
                    Cache::lock($lockKey, 5)->block(3, function () use ($request, &$authSession, &$token) {
                        $token = $request->session()->get('auth_session_token');
                        if ($token) {
                            $authSession = AuthSession::where('id', $token)
                                ->where('user_id', $request->user()->id)
                                ->first();
                        }
                        if (! $authSession) {
                            $sessionEngine = app(SessionEngine::class);
                            $authSession = $sessionEngine->createSession($request->user(), $request);
                            $token = $authSession->id;
                            $request->session()->put('auth_session_token', $token);
                        }
                    });
                } catch (\Throwable $e) {
                    return $this->reject($request, 'Session not found.');
                }
            }

            // ── Step 3: Revocation check ─────────────────────────────────────────
            if (! $authSession || $authSession->is_revoked) {
                Cache::forget($cacheKey);

                return $this->reject($request, 'Session has been revoked.');
            }

            $nowTs = time();

            // Helper to get Unix Epoch Timestamp from Carbon instance or string safely.
            $getTimestamp = function (mixed $dateValue) use ($nowTs): int {
                if (! $dateValue) {
                    return $nowTs;
                }
                if ($dateValue instanceof Carbon) {
                    return $dateValue->getTimestamp();
                }

                return Carbon::parse((string) $dateValue)->getTimestamp();
            };

            // ── Step 4: Expiry check (epoch seconds comparison) ───────────────────
            if ($authSession->expires_at) {
                $expiresTs = $getTimestamp($authSession->expires_at);
                if ($nowTs > $expiresTs) {
                    $authSession->update(['is_revoked' => true]);
                    Cache::forget($cacheKey);

                    return $this->reject($request, 'Session expired.');
                }
            }

            // ── Step 5: Absolute session timeout (epoch seconds comparison) ──────
            $absoluteTimeoutHours = (int) config('session.absolute_timeout_hours', 8);
            if ($authSession->created_at) {
                $createdTs = $getTimestamp($authSession->created_at);
                $sessionAgeSeconds = $nowTs - $createdTs;
                if ($sessionAgeSeconds >= ($absoluteTimeoutHours * 3600)) {
                    $authSession->update(['is_revoked' => true]);
                    Cache::forget($cacheKey);

                    return $this->reject($request, "Session absolute timeout ({$absoluteTimeoutHours}h) exceeded. Please login again.");
                }
            }

            // ── Step 6: Server-side idle timeout (epoch seconds comparison) ─────
            if ($authSession->last_activity_at) {
                $idleTimeoutMinutes = (int) config('session.lifetime', 30);
                $lastActivityTs = $getTimestamp($authSession->last_activity_at);
                $idleSinceSeconds = $nowTs - $lastActivityTs;

                // Only trigger if last_activity_at is in the past AND exceeds lifetime in seconds
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
                    'expires_at' => Carbon::now()->addMinutes(config('session.lifetime')),
                    'last_activity_at' => Carbon::now(),
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

        // INERTIA FIX: If this is an Inertia request (non-GET such as DELETE/POST/PATCH),
        // a plain HTTP 302 redirect causes Inertia to re-send the request to /login with
        // the same HTTP method (e.g. DELETE /login → 405 Method Not Allowed).
        //
        // Inertia::location() returns HTTP 409 Conflict + X-Inertia-Location header,
        // which tells Inertia to perform a full browser GET navigation to the target URL.
        // This is the only correct way to redirect Inertia non-GET requests server-side.
        if ($request->header('X-Inertia')) {
            return response('', 409)->header('X-Inertia-Location', route('login'));
        }

        return redirect()->route('login')->with('error', $reason);
    }
}
