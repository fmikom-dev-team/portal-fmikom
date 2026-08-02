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
 */
class SecureSession
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            $token = $this->ensureSessionToken($request);
            if ($token) {
                $authSession = $this->resolveAuthSession($request, $token);
                if (! $authSession || $authSession->is_revoked) {
                    Cache::forget('auth_sess_'.$token);

                    return $this->reject($request, 'Session has been revoked.');
                }

                $rejectionReason = $this->validateSessionTimeouts($authSession);
                if ($rejectionReason !== null) {
                    $authSession->update(['is_revoked' => true]);
                    Cache::forget('auth_sess_'.$token);

                    return $this->reject($request, $rejectionReason);
                }

                $this->updateSessionActivity($authSession, 'auth_sess_'.$token);
                $request->attributes->set('_session_risk_score', $authSession->risk_score);
            }
        }

        return $next($request);
    }

    protected function ensureSessionToken(Request $request): ?string
    {
        $token = $request->session()->get('auth_session_token');
        if ($token) {
            return (string) $token;
        }

        $lockKey = 'create_auth_sess_'.$request->user()->id;
        try {
            Cache::lock($lockKey, 5)->block(3, function () use ($request, &$token) {
                $token = $request->session()->get('auth_session_token');
                if (! $token) {
                    /** @var SessionEngine $sessionEngine */
                    $sessionEngine = app(SessionEngine::class);
                    $authSession = $sessionEngine->createSession($request->user(), $request);
                    $token = $authSession->id;
                    $request->session()->put('auth_session_token', $token);
                }
            });
        } catch (\Throwable $e) {
            // Silently allow request to proceed
        }

        return $token ? (string) $token : null;
    }

    protected function resolveAuthSession(Request $request, string $token): ?AuthSession
    {
        $cacheKey = 'auth_sess_'.$token;
        /** @var AuthSession|null $authSession */
        $authSession = Cache::remember($cacheKey, 30, fn () => AuthSession::where('id', $token)
            ->where('user_id', $request->user()->id)
            ->first());

        if (! $authSession) {
            Cache::forget($cacheKey);
            $lockKey = 'create_auth_sess_'.$request->user()->id;
            try {
                Cache::lock($lockKey, 5)->block(3, function () use ($request, &$authSession, &$token) {
                    $token = (string) $request->session()->get('auth_session_token');
                    if ($token) {
                        /** @var AuthSession|null $authSession */
                        $authSession = AuthSession::where('id', $token)
                            ->where('user_id', $request->user()->id)
                            ->first();
                    }
                    if (! $authSession) {
                        /** @var SessionEngine $sessionEngine */
                        $sessionEngine = app(SessionEngine::class);
                        $authSession = $sessionEngine->createSession($request->user(), $request);
                        $token = $authSession->id;
                        $request->session()->put('auth_session_token', $token);
                    }
                });
            } catch (\Throwable $e) {
                return null;
            }
        }

        return $authSession;
    }

    protected function validateSessionTimeouts(AuthSession $authSession): ?string
    {
        $nowTs = time();
        $reason = null;

        $getTimestamp = function (mixed $dateValue) use ($nowTs): int {
            if (! $dateValue) {
                return $nowTs;
            }
            if ($dateValue instanceof Carbon) {
                return $dateValue->getTimestamp();
            }

            return Carbon::parse((string) $dateValue)->getTimestamp();
        };

        if ($nowTs > $getTimestamp($authSession->expires_at)) {
            $reason = 'Session expired.';
        } else {
            $absoluteTimeoutHours = (int) config('session.absolute_timeout_hours', 8);
            if (($nowTs - $getTimestamp($authSession->created_at)) >= ($absoluteTimeoutHours * 3600)) {
                $reason = "Session absolute timeout ({$absoluteTimeoutHours}h) exceeded. Please login again.";
            } else {
                $idleTimeoutMinutes = (int) config('session.lifetime', 30);
                if (($nowTs - $getTimestamp($authSession->last_activity_at)) >= ($idleTimeoutMinutes * 60)) {
                    $reason = "Session idle timeout ({$idleTimeoutMinutes}m) exceeded.";
                }
            }
        }

        return $reason;
    }

    protected function updateSessionActivity(AuthSession $authSession, string $cacheKey): void
    {
        $activityKey = 'sess_act_'.$authSession->id;
        if (Cache::add($activityKey, true, 10)) {
            $authSession->update([
                'expires_at' => Carbon::now()->addMinutes(config('session.lifetime')),
                'last_activity_at' => Carbon::now(),
            ]);
            Cache::forget($cacheKey);
        }
    }

    protected function reject(Request $request, string $reason): mixed
    {
        $request->session()->forget('auth_session_token');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Session invalid: '.$reason], 401);
        }

        if ($request->header('X-Inertia')) {
            return response('', 409)->header('X-Inertia-Location', route('login'));
        }

        return redirect()->route('login')->with('error', $reason);
    }
}
