<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Auth\AuthAuditLog;
use App\Models\Auth\AuthMfa;
use App\Models\Auth\AuthOAuthProvider;
use App\Models\Auth\AuthSession;
use App\Models\Auth\AuthSetting;
use App\Models\Module;
use App\Models\User;
use App\Modules\WorkOs\Services\AuthPlatform\JwtTemplateCompiler;
use App\Modules\WorkOs\Services\Sessions\AuthSessionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class AuthenticationController extends Controller
{
    protected AuthSessionService $sessionService;

    public function __construct(AuthSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    // =========================================================================
    // LOGIN METHODS — Real DB-backed configuration
    // =========================================================================

    public function methods()
    {
        $settings = AuthSetting::getAllTyped();

        return response()->json([
            'methods' => [
                'email_password' => [
                    'enabled' => $settings['email_password.enabled'] ?? true,
                    'min_length' => $settings['email_password.min_length'] ?? 10,
                    'complexity' => $settings['email_password.complexity'] ?? 3,
                    'require_uppercase' => $settings['email_password.require_uppercase'] ?? false,
                    'require_lowercase' => $settings['email_password.require_lowercase'] ?? false,
                    'require_number' => $settings['email_password.require_number'] ?? false,
                    'require_special' => $settings['email_password.require_special'] ?? false,
                ],
                'passkeys' => ['enabled' => $settings['passkeys.enabled'] ?? true],
                'magic_links' => ['enabled' => $settings['magic_links.enabled'] ?? true],
                'sso' => ['enabled' => $settings['sso.enabled'] ?? true],
                'mfa' => [
                    'required' => $settings['mfa.required'] ?? false,
                    'totp_enabled' => $settings['mfa.totp.enabled'] ?? true,
                    'sms_enabled' => $settings['mfa.sms.enabled'] ?? false,
                ],
            ],
            'session' => [
                'ttl_days' => $settings['session.ttl_days'] ?? 7,
                'inactivity_minutes' => $settings['session.inactivity_minutes'] ?? 60,
                'concurrent_limit' => $settings['session.concurrent_limit'] ?? 5,
            ],
            'password' => [
                'reject_breached' => $settings['password.reject_breached'] ?? true,
                'history_count' => $settings['password.history_count'] ?? 5,
                'expiration_days' => $settings['password.expiration_days'] ?? 90,
            ],
        ]);
    }

    /**
     * Update a login method toggle or setting value.
     */
    public function updateMethod(Request $request)
    {
        $request->validate([
            'key' => 'nullable|string|max:100',
            'value' => 'nullable',
            'settings' => 'nullable|array',
        ]);

        if ($request->filled('settings')) {
            foreach ($request->settings as $k => $v) {
                AuthSetting::set($k, $v);
                AuthAuditLog::log('auth.setting.changed', $request->user()?->id, [
                    'key' => $k,
                    'value' => $v,
                ]);
            }

            return response()->json(['message' => 'Settings updated.']);
        }

        AuthSetting::set($request->key, $request->value);

        // Audit log the change
        AuthAuditLog::log('auth.setting.changed', $request->user()?->id, [
            'key' => $request->key,
            'value' => $request->value,
        ]);

        return response()->json(['message' => 'Setting updated.', 'key' => $request->key]);
    }

    // =========================================================================
    // FEATURES — Real DB-backed configuration
    // =========================================================================

    public function features()
    {
        $settings = AuthSetting::getAllTyped();

        return response()->json([
            'features' => [
                'hosted_ui' => [
                    'enabled' => $settings['feature.hosted_ui.enabled'] ?? true,
                    'idp_sso' => $settings['feature.hosted_ui.idp_sso'] ?? true,
                    'redirect_uri' => config('app.url').'/auth/callback',
                    'authkit_url' => $settings['feature.hosted_ui.authkit_url'] ?? '',
                ],
                'sign_up' => [
                    'enabled' => $settings['feature.sign_up.enabled'] ?? true,
                ],
                'invitations' => [
                    'enabled' => $settings['feature.invitations.enabled'] ?? true,
                    'expiry_days' => (int) ($settings['feature.invitations.expiry_days'] ?? 7),
                ],
                'mfa' => [
                    'mode' => $settings['feature.mfa.mode'] ?? 'off', // off | optional | required
                ],
                'localization' => [
                    'enabled' => $settings['feature.localization.enabled'] ?? true,
                    'fallback_language' => $settings['feature.localization.fallback_language'] ?? 'en-US',
                ],
                'user_impersonation' => [
                    'enabled' => $settings['feature.user_impersonation.enabled'] ?? false,
                ],
            ],
        ]);
    }

    public function updateFeature(Request $request)
    {
        $request->validate([
            'key' => 'nullable|string|max:100',
            'value' => 'nullable',
            'settings' => 'nullable|array',
        ]);

        if ($request->filled('settings')) {
            foreach ($request->settings as $k => $v) {
                AuthSetting::set($k, $v);
                AuthAuditLog::log('auth.feature.changed', $request->user()?->id, [
                    'key' => $k,
                    'value' => $v,
                ]);
            }

            return response()->json(['message' => 'Feature settings updated.']);
        }

        AuthSetting::set($request->key, $request->value);
        AuthAuditLog::log('auth.feature.changed', $request->user()?->id, [
            'key' => $request->key,
            'value' => $request->value,
        ]);

        return response()->json(['message' => 'Feature updated.']);
    }

    public function providers()
    {
        $providers = AuthOAuthProvider::query()->orderBy('name', 'asc')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'is_enabled' => (bool) $p->is_enabled,
                'use_demo' => (bool) $p->use_demo_credentials,
                'has_custom' => ! empty($p->client_id),
                'client_id' => $p->client_id,
                'scopes' => $p->scopes,
                'redirect_uri' => url("/auth/oauth/{$p->slug}/callback", []),
            ];
        });

        return response()->json(['providers' => $providers]);
    }

    public function updateProvider(Request $request, string $slug)
    {
        $request->validate([
            'is_enabled' => 'sometimes|boolean',
            'use_demo' => 'sometimes|boolean',
            'client_id' => 'sometimes|nullable|string|max:500',
            'client_secret' => 'sometimes|nullable|string|max:2000',
            'scopes' => 'sometimes|nullable|string',
        ]);

        $provider = AuthOAuthProvider::query()->where('slug', '=', $slug)->firstOrFail();

        $data = [];

        if ($request->has('is_enabled')) {
            $data['is_enabled'] = $request->boolean('is_enabled');
        }
        if ($request->has('use_demo')) {
            $data['use_demo_credentials'] = $request->boolean('use_demo');
        }
        if ($request->has('client_id')) {
            $data['client_id'] = $request->client_id;
        }
        if ($request->has('scopes')) {
            $data['scopes'] = $request->scopes;
        }

        // Encrypt client secret before storing
        if ($request->filled('client_secret')) {
            $data['client_secret'] = Crypt::encryptString($request->client_secret);
        }

        $provider->fill($data)->save();

        // Bust cache
        Cache::forget("oauth_provider.{$slug}");

        AuthAuditLog::log('auth.provider.toggled', $request->user()?->id, [
            'provider' => $slug,
            'is_enabled' => $data['is_enabled'] ?? null,
        ]);

        return response()->json([
            'message' => 'Provider updated.',
            'provider' => array_merge($provider->fresh()->toArray(), [
                'redirect_uri' => url("/auth/oauth/{$slug}/callback"),
            ]),
        ]);
    }

    // =========================================================================
    // SESSIONS — Real, with filtering & pagination
    // =========================================================================

    public function sessions(Request $request)
    {
        $payload = $this->sessionService->getSessionsData($request);

        return response()->json($payload);
    }

    public function revokeSession(Request $request, AuthSession $session)
    {
        if ($session->session_token) {
            try {
                Session::getHandler()->destroy($session->session_token);
            } catch (\Throwable $e) {
                // Ignore errors
            }
        }

        $session->fill(['is_revoked' => true])->save();

        AuthAuditLog::log('auth.session.revoked', $request->user()?->id, [
            'session_id' => $session->id,
            'target_user_id' => $session->user_id,
        ], 'warning');

        Cache::forget('auth.session.stats');
        Cache::flush();

        return response()->json(['message' => 'Session revoked.']);
    }

    public function revokeAllSessions(Request $request)
    {
        $activeSessions = AuthSession::where('is_revoked', '=', false, 'and')->get();
        $sessionHandler = Session::getHandler();

        foreach ($activeSessions as $s) {
            if ($s->session_token) {
                try {
                    $sessionHandler->destroy($s->session_token);
                } catch (\Throwable $e) {
                    // Ignore errors
                }
            }
            $s->fill(['is_revoked' => true])->save();
        }

        AuthAuditLog::log('auth.session.revoke_all', $request->user()?->id, [
            'count' => count($activeSessions),
        ], 'warning');

        Cache::forget('auth.session.stats');
        Cache::flush();

        return response()->json(['message' => count($activeSessions).' sessions revoked.']);
    }

    public function sessionsConfig()
    {
        $settings = AuthSetting::getAllTyped();

        $users = User::take(5)->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        });

        return response()->json([
            'lifetime' => [
                'maxLength' => (int) ($settings['session.lifetime.max_length'] ?? 365),
                'maxUnit' => $settings['session.lifetime.max_unit'] ?? 'Days',
                'tokenDuration' => (int) ($settings['session.lifetime.token_duration'] ?? 5),
                'tokenUnit' => $settings['session.lifetime.token_unit'] ?? 'Minutes',
                'inactivityLength' => (int) ($settings['session.lifetime.inactivity_length'] ?? 2),
                'inactivityUnit' => $settings['session.lifetime.inactivity_unit'] ?? 'Days',
            ],
            'corsOrigins' => $settings['session.cors.origins'] ?? [],
            'jwtCode' => $settings['session.jwt.template'] ?? null,
            'users' => $users,
        ]);
    }

    public function updateSessionsConfig(Request $request)
    {
        $request->validate([
            'lifetime' => 'sometimes|array',
            'corsOrigins' => 'sometimes|array',
            'jwtCode' => 'sometimes|nullable|string',
        ]);

        $user = $request->user();

        if ($request->has('lifetime')) {
            $lf = $request->lifetime;
            AuthSetting::set('session.lifetime.max_length', (int) ($lf['maxLength'] ?? 365));
            AuthSetting::set('session.lifetime.max_unit', $lf['maxUnit'] ?? 'Days');
            AuthSetting::set('session.lifetime.token_duration', (int) ($lf['tokenDuration'] ?? 5));
            AuthSetting::set('session.lifetime.token_unit', $lf['tokenUnit'] ?? 'Minutes');
            AuthSetting::set('session.lifetime.inactivity_length', (int) ($lf['inactivityLength'] ?? 2));
            AuthSetting::set('session.lifetime.inactivity_unit', $lf['inactivityUnit'] ?? 'Days');

            AuthAuditLog::log('auth.session_lifetime.changed', $user?->id, [
                'maxLength' => $lf['maxLength'] ?? 365,
                'maxUnit' => $lf['maxUnit'] ?? 'Days',
                'tokenDuration' => $lf['tokenDuration'] ?? 5,
                'tokenUnit' => $lf['tokenUnit'] ?? 'Minutes',
                'inactivityLength' => $lf['inactivityLength'] ?? 2,
                'inactivityUnit' => $lf['inactivityUnit'] ?? 'Days',
            ]);
        }

        if ($request->has('corsOrigins')) {
            AuthSetting::set('session.cors.origins', $request->corsOrigins);

            AuthAuditLog::log('auth.session_cors.changed', $user?->id, [
                'origins' => $request->corsOrigins,
            ]);
        }

        if ($request->has('jwtCode')) {
            AuthSetting::set('session.jwt.template', $request->jwtCode);

            AuthAuditLog::log('auth.session_jwt.changed', $user?->id, [
                'template_length' => strlen($request->jwtCode),
            ]);
        }

        return response()->json(['message' => 'Settings saved successfully.']);
    }

    public function previewJwt(Request $request, JwtTemplateCompiler $compiler)
    {
        $request->validate([
            'jwtCode' => 'required|string',
            'userId' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->userId);
        $module = Module::query()->first();

        $result = $compiler->compile($request->jwtCode, $user, $module);

        return response()->json($result);
    }

    // =========================================================================
    // MFA PLATFORM STATUS
    // =========================================================================

    public function mfaStatus()
    {
        $totalUsers = User::query()->count('*');
        $mfaUsers = AuthMfa::query()->where('is_active', '=', true, 'and')->distinct()->count('user_id');

        return response()->json([
            'total_users' => $totalUsers,
            'mfa_users' => $mfaUsers,
            'adoption_rate' => $totalUsers > 0 ? round(($mfaUsers / $totalUsers) * 100, 1) : 0,
            'required' => AuthSetting::get('mfa.required', false),
            'totp_enabled' => AuthSetting::get('mfa.totp.enabled', true),
            'sms_enabled' => AuthSetting::get('mfa.sms.enabled', false),
        ]);
    }

    // =========================================================================
    // AUDIT LOGS
    // =========================================================================

    public function auditLogs(Request $request)
    {
        $query = AuthAuditLog::with('user:id,name,email')
            ->orderByDesc('occurred_at');

        if ($request->filled('event')) {
            $query->where('event', 'like', '%'.$request->event.'%');
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('days')) {
            $query->where('occurred_at', '>=', Carbon::now()->subDays((int) $request->days));
        }

        return response()->json([
            'logs' => $query->paginate(50),
        ]);
    }

    // =========================================================================
    // PASSWORD POLICIES
    // =========================================================================

    public function passwordPolicies()
    {
        return response()->json([
            'policy' => AuthSetting::getGroup('password'),
        ]);
    }

    public function updatePasswordPolicy(Request $request)
    {
        $request->validate([
            'key' => 'required|string|starts_with:password.',
            'value' => 'required',
        ]);

        AuthSetting::set($request->key, $request->value);

        AuthAuditLog::log('auth.setting.changed', $request->user()?->id, [
            'key' => $request->key,
            'value' => $request->value,
        ]);

        return response()->json(['message' => 'Password policy updated.']);
    }
}
