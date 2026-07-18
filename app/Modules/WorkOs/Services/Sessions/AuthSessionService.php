<?php

namespace App\Modules\WorkOs\Services\Sessions;

use App\Models\Auth\AuthLoginAttempt;
use App\Models\Auth\AuthSession;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use SessionHandlerInterface;

class AuthSessionService
{
    /**
     * Fetch, filter, and format session data.
     */
    public function getSessionsData(Request $request): array
    {
        $query = AuthSession::with(['user:id,name,email', 'device'])
            ->orderBy('last_activity_at', 'desc');

        $this->applySessionFilters($query, $request);

        $paginator = $query->paginate(20);

        $sessionHandler = Session::getHandler();
        $isDriverCheckable = config('session.driver') !== 'array' && (! app()->runningUnitTests() || config('session.test_driver_checkable', false));

        if (! $request->boolean('revoked')) {
            $this->validateActiveSessions($paginator, $sessionHandler, $isDriverCheckable);
        }

        $sessions = $paginator->through(function (AuthSession $s) use ($sessionHandler, $isDriverCheckable) {
            return $this->mapSessionPayload($s, $sessionHandler, $isDriverCheckable);
        });

        $stats = Cache::remember('auth.session.stats', 60, function () {
            return [
                'total_active' => AuthSession::query()->where('is_revoked', '=', false, 'and')->count('*'),
                'total_revoked' => AuthSession::query()->where('is_revoked', '=', true, 'and')->count('*'),
                'high_risk' => AuthSession::query()->where('is_revoked', '=', false, 'and')->where('risk_score', '>', 60, 'and')->count('*'),
            ];
        });

        return ['sessions' => $sessions, 'stats' => $stats];
    }

    private function applySessionFilters(Builder $query, Request $request): void
    {
        if ($request->boolean('revoked')) {
            $query->where('is_revoked', '=', true);
        } else {
            $query->where('is_revoked', '=', false);
        }

        if ($request->filled('risk_level')) {
            match ($request->risk_level) {
                'high' => $query->where('risk_score', '>', 60),
                'medium' => $query->whereBetween('risk_score', [21, 60]),
                'safe' => $query->where('risk_score', '<=', 20),
                default => null,
            };
        }
    }

    private function validateActiveSessions(LengthAwarePaginator $paginator, SessionHandlerInterface $sessionHandler, bool $isDriverCheckable): void
    {
        foreach ($paginator as $s) {
            $isActive = false;
            if ($isDriverCheckable && $s->session_token) {
                try {
                    $sessionData = $sessionHandler->read($s->session_token);
                    $isActive = ! empty($sessionData);
                } catch (\Throwable $e) {
                    $isActive = $s->expires_at && now()->isBefore($s->expires_at);
                }
            } else {
                $isActive = $s->expires_at && now()->isBefore($s->expires_at);
            }

            if (! $isActive) {
                $s->fill(['is_revoked' => true])->save();
            }
        }
    }

    private function mapSessionPayload(AuthSession $s, SessionHandlerInterface $sessionHandler, bool $isDriverCheckable): array
    {
        $user = $s->user;
        $org = 'Portal';
        $auth = 'Password';

        if ($user) {
            $org = $this->resolveUserOrganization($user);
            $auth = $this->resolveAuthenticationMethod($user, $s);
        }

        $app = $this->resolveActiveApplication($s, $sessionHandler, $isDriverCheckable);
        $riskLevel = $this->resolveRiskLevel($s->risk_score);

        return [
            'id' => $s->id,
            'user' => $s->user,
            'device' => $s->device,
            'ip_address' => $s->ip_address,
            'geolocation' => $s->geolocation,
            'risk_score' => $s->risk_score,
            'risk_level' => $riskLevel,
            'is_revoked' => $s->is_revoked,
            'last_activity_at' => $s->last_activity_at?->toISOString(),
            'expires_at' => $s->expires_at?->toISOString(),
            'created_at' => $s->created_at?->toISOString(),
            'organization' => $org,
            'authentication' => $auth,
            'application' => $app,
        ];
    }

    private function resolveUserOrganization(User $user): string
    {
        $modules = $user->moduleRoles()->with('module')->get()->pluck('module.code')->unique()->toArray();

        return ! empty($modules) ? implode(', ', $modules) : 'Portal';
    }

    private function resolveAuthenticationMethod(User $user, AuthSession $s): string
    {
        $attempt = AuthLoginAttempt::query()
            ->where('email', '=', $user->email, 'and')
            ->where('is_successful', '=', true, 'and')
            ->where('created_at', '<=', $s->created_at->addSeconds(5), 'and')
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $attempt) {
            return 'Password';
        }

        return match ($attempt->provider) {
            'password' => 'Password',
            'google' => 'Google OAuth',
            default => ucfirst((string) $attempt->provider),
        };
    }

    private function resolveActiveApplication(AuthSession $s, SessionHandlerInterface $sessionHandler, bool $isDriverCheckable): string
    {
        if (! $isDriverCheckable || ! $s->session_token) {
            return 'Portal FMIKOM';
        }

        try {
            $sessionData = $sessionHandler->read($s->session_token);
            $activeModule = $this->extractActiveModule($sessionData);

            return $activeModule !== null ? strtoupper($activeModule) : 'Portal FMIKOM';
        } catch (\Throwable $e) {
            // Ignore session read errors
            return 'Portal FMIKOM';
        }
    }

    /**
     * Extract the active_module key from raw session data (handles encrypted sessions).
     */
    private function extractActiveModule(string $sessionData): ?string
    {
        if (empty($sessionData)) {
            return null;
        }

        // [FIX PHP Object Injection] Set allowed_classes to false to prevent object instantiation
        $unserialized = @unserialize($sessionData, ['allowed_classes' => false]);

        if ($unserialized === false && config('session.encrypt')) {
            try {
                $decrypted = Crypt::decrypt($sessionData);
                $unserialized = @unserialize($decrypted, ['allowed_classes' => false]);
            } catch (\Throwable $e) {
                // Ignore decryption errors
                return null;
            }
        }

        if (is_array($unserialized) && ! empty($unserialized['active_module'])) {
            return (string) $unserialized['active_module'];
        }

        return null;
    }

    private function resolveRiskLevel(int $riskScore): string
    {
        if ($riskScore > 60) {
            return 'high';
        }
        if ($riskScore > 20) {
            return 'medium';
        }

        return 'safe';
    }
}
