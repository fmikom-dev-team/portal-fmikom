<?php

namespace App\Modules\WorkOs\Controllers\Concerns;

use App\Models\Auth\AuthEmailLog;
use App\Models\Auth\AuthLoginAttempt;
use App\Models\Auth\AuthSession;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;

trait HasUserDiagnostics
{
    public function sessions(User $user)
    {
        // Fetch active auth_sessions
        $activeSessions = AuthSession::query()->where('user_id', '=', $user->id, 'and')
            ->where('is_revoked', '=', false, 'and')
            ->get();

        $sessionHandler = Session::getHandler();
        // Fallback to time-based checking in testing or array driver, since array driver doesn't persist sessions
        $isDriverCheckable = config('session.driver') !== 'array' && (! app()->runningUnitTests() || config('session.test_driver_checkable', false));

        foreach ($activeSessions as $session) {
            $isActive = false;
            if ($isDriverCheckable && $session->session_token) {
                try {
                    $sessionData = $sessionHandler->read($session->session_token);
                    $isActive = ! empty($sessionData);
                } catch (\Throwable $e) {
                    $isActive = $session->expires_at && now()->isBefore($session->expires_at);
                }
            } else {
                $isActive = $session->expires_at && now()->isBefore($session->expires_at);
            }

            if (! $isActive) {
                $session->fill(['is_revoked' => true]);
                $session->save();
            }
        }

        // Retrieve and return all synced sessions with their linked device info
        $sessions = AuthSession::query()->with('device')
            ->where('user_id', '=', $user->id, 'and')
            ->latest('last_activity_at')
            ->get()
            ->map(function ($s) use ($user, $sessionHandler, $isDriverCheckable) {
                return $this->formatSessionPayload($s, $user, $sessionHandler, $isDriverCheckable);
            });

        return response()->json([
            'sessions' => $sessions,
        ]);
    }

    public function revokeSession(User $user, int|string $sessionId)
    {
        $session = AuthSession::query()->where('user_id', '=', $user->id, 'and')
            ->where('id', '=', $sessionId, 'and')
            ->firstOrFail();

        // Delete the corresponding Laravel active session from the handler so they are logged out!
        if ($session->session_token) {
            try {
                Session::getHandler()->destroy($session->session_token);
            } catch (\Throwable $e) {
                // Ignore errors
            }
        }

        $session->fill(['is_revoked' => true]);
        $session->save();

        return response()->json([
            'success' => true,
            'message' => 'Session revoked successfully.',
        ]);
    }

    public function revokeAllSessions(User $user)
    {
        // Fetch active auth_sessions
        $activeSessions = AuthSession::query()->where('user_id', '=', $user->id, 'and')
            ->where('is_revoked', '=', false, 'and')
            ->get();

        $sessionHandler = Session::getHandler();

        foreach ($activeSessions as $session) {
            if ($session->session_token) {
                try {
                    $sessionHandler->destroy($session->session_token);
                } catch (\Throwable $e) {
                    // Ignore errors
                }
            }
            $session->fill(['is_revoked' => true]);
            $session->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'All active sessions revoked successfully.',
        ]);
    }

    public function clearInactiveSessions(User $user)
    {
        AuthSession::query()->where('user_id', '=', $user->id, 'and')
            ->where(function ($query) {
                $query->where('is_revoked', '=', true, 'and')
                    ->orWhere('expires_at', '<', now());
            })
            ->{'delete'}();

        return response()->json([
            'success' => true,
            'message' => 'Inactive sessions cleared successfully.',
        ]);
    }

    public function emails(User $user)
    {
        $logs = AuthEmailLog::query()->where('user_id', '=', $user->id, 'and')
            ->latest('created_at')
            ->get();

        return response()->json([
            'emails' => $logs,
        ]);
    }

    public function clearEmailHistory(User $user)
    {
        AuthEmailLog::query()->where('user_id', '=', $user->id, 'and')->{'delete'}();

        return response()->json([
            'success' => true,
            'message' => 'Email history cleared successfully.',
        ]);
    }

    private function formatSessionPayload(AuthSession $s, User $user, \SessionHandlerInterface $sessionHandler, bool $isDriverCheckable): array
    {
        // Determine organization (user's assigned modules)
        $modules = $user->moduleRoles()->with('module')->get()->pluck('module.code')->unique()->toArray();
        $org = ! empty($modules) ? implode(', ', $modules) : 'Portal';

        $auth = $this->determineAuthenticationMethod($user, $s);
        $app = $this->resolveActiveApplication($s, $sessionHandler, $isDriverCheckable);

        return array_merge($s->toArray(), [
            'organization' => $org,
            'authentication' => $auth,
            'application' => $app,
        ]);
    }

    /**
     * Determine authentication method from login attempts.
     */
    private function determineAuthenticationMethod(User $user, AuthSession $s): string
    {
        $attempt = AuthLoginAttempt::query()->where('email', '=', $user->email, 'and')
            ->where('is_successful', '=', true, 'and')
            ->where('created_at', '<=', $s->created_at->addSeconds(5))
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $attempt) {
            return 'Password';
        }

        return match ($attempt->provider) {
            'password' => 'Password',
            'google' => 'Google OAuth',
            default => ucfirst($attempt->provider),
        };
    }

    /**
     * Dynamically resolve Application (Active Module) from Laravel Session Store.
     */
    private function resolveActiveApplication(AuthSession $s, \SessionHandlerInterface $sessionHandler, bool $isDriverCheckable): string
    {
        if (! $isDriverCheckable || ! $s->session_token) {
            return 'Portal FMIKOM';
        }

        try {
            $sessionData = $sessionHandler->read($s->session_token);
            if (! empty($sessionData)) {
                $unserialized = $this->unserializeSessionData($sessionData);
                if (is_array($unserialized) && ! empty($unserialized['active_module'])) {
                    return strtoupper($unserialized['active_module']);
                }
            }
        } catch (\Throwable $e) {
            // Ignore exception
        }

        return 'Portal FMIKOM';
    }

    /**
     * Helper to unserialize and optionally decrypt session data.
     */
    private function unserializeSessionData(string $sessionData)
    {
        // [FIX PHP Object Injection] Set allowed_classes to false to prevent object instantiation
        $unserialized = @unserialize($sessionData, ['allowed_classes' => false]);
        if ($unserialized === false && config('session.encrypt')) {
            try {
                $decrypted = Crypt::decrypt($sessionData);
                $unserialized = @unserialize($decrypted, ['allowed_classes' => false]);
            } catch (\Throwable $e) {
                // Ignore exception
            }
        }

        return $unserialized;
    }
}
