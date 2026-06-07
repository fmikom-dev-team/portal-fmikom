<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuthSession;
use App\Models\AuthLoginAttempt;
use App\Models\AuthSetting;
use App\Models\AuthOAuthProvider;
use App\Models\AuthMfa;
use App\Models\AuthAuditLog;
use App\Models\AuthPasskey;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class AuthenticationController extends Controller
{
    // =========================================================================
    // ANALYTICS
    // =========================================================================

    public function analytics(Request $request)
    {
        $days      = min((int) $request->get('days', 7), 365);
        $interval  = $request->get('interval', 'daily'); // daily, weekly
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        // 1. User Stats
        $totalUsers = User::count();
        $newUsers   = User::where('created_at', '>=', $startDate)->count();

        // 2. Active sessions in period
        $activeSessions = AuthSession::where('last_activity_at', '>=', $startDate)
            ->where('is_revoked', false)
            ->distinct('user_id')
            ->count('user_id');

        // 3. Login Attempts
        $attempts = AuthLoginAttempt::where('created_at', '>=', $startDate)->get();
        $totalAttempts   = $attempts->count();
        $successAttempts = $attempts->where('is_successful', true)->count();
        $failedAttempts  = $totalAttempts - $successAttempts;
        $successRate     = $totalAttempts > 0 ? round(($successAttempts / $totalAttempts) * 100, 1) : 0;

        // 4. MFA Adoption
        $mfaUsers    = AuthMfa::where('is_active', true)->distinct('user_id')->count('user_id');
        $mfaAdoption = $totalUsers > 0 ? round(($mfaUsers / $totalUsers) * 100, 1) : 0;

        // 5. Passkeys Registered
        $passkeyUsers = AuthPasskey::distinct('user_id')->count('user_id');

        // 6. Provider Distribution
        $providerStats = AuthLoginAttempt::where('created_at', '>=', $startDate)
            ->whereNotNull('provider')
            ->select('provider', DB::raw('count(*) as count'))
            ->groupBy('provider')
            ->pluck('count', 'provider')
            ->toArray();

        // 7. Daily/Weekly Trends for chart
        $groupFormat = $interval === 'weekly' ? '%Y-%u' : '%Y-%m-%d';
        $trends = AuthLoginAttempt::select(
                DB::raw("DATE_FORMAT(created_at, '{$groupFormat}') as period"),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN is_successful = 1 THEN 1 ELSE 0 END) as successful'),
                DB::raw('SUM(CASE WHEN is_successful = 0 THEN 1 ELSE 0 END) as failed')
            )
            ->where('created_at', '>=', $startDate)
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        // 8. Risk distribution of active sessions
        $riskDistribution = [
            'safe'   => AuthSession::where('is_revoked', false)->where('risk_score', '<=', 20)->count(),
            'medium' => AuthSession::where('is_revoked', false)->whereBetween('risk_score', [21, 60])->count(),
            'high'   => AuthSession::where('is_revoked', false)->where('risk_score', '>', 60)->count(),
        ];

        // 9. Real-time detailed lists for interactive drill-down
        $activeSessionsList = AuthSession::with(['user:id,name,email', 'device'])
            ->where('last_activity_at', '>=', $startDate)
            ->where('is_revoked', false)
            ->orderByDesc('last_activity_at')
            ->take(10)
            ->get()
            ->map(function ($s) {
                return [
                    'id'         => $s->id,
                    'user_name'  => $s->user?->name ?? 'Unknown',
                    'user_email' => $s->user?->email ?? '-',
                    'ip_address' => $s->ip_address,
                    'device'     => $s->device?->browser ?? 'Unknown',
                    'risk_level' => $s->risk_score > 60 ? 'high' : ($s->risk_score > 20 ? 'medium' : 'safe'),
                    'last_active'=> $s->last_activity_at?->diffForHumans() ?? '-',
                ];
            });

        $newUsersList = User::where('created_at', '>=', $startDate)
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(function ($u) {
                return [
                    'id'         => $u->id,
                    'name'       => $u->name,
                    'email'      => $u->email,
                    'user_type'  => $u->user_type,
                    'created_at' => $u->created_at?->diffForHumans() ?? '-',
                ];
            });

        $failedLoginsList = AuthLoginAttempt::where('created_at', '>=', $startDate)
            ->where('is_successful', false)
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(function ($attempt) {
                return [
                    'id'         => $attempt->id,
                    'email'      => $attempt->email ?? 'Unknown',
                    'ip_address' => $attempt->ip_address ?? '-',
                    'provider'   => $attempt->provider ?? 'Email/Password',
                    'reason'     => $attempt->failure_reason ?? 'Invalid credentials',
                    'time'       => $attempt->created_at?->diffForHumans() ?? '-',
                ];
            });

        $data = compact(
            'totalUsers', 'newUsers', 'activeSessions',
            'totalAttempts', 'successAttempts', 'failedAttempts', 'successRate',
            'mfaAdoption', 'mfaUsers', 'passkeyUsers',
            'providerStats', 'trends', 'riskDistribution',
            'activeSessionsList', 'newUsersList', 'failedLoginsList'
        );

        return response()->json($data);
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
                    'enabled'           => $settings['email_password.enabled'] ?? true,
                    'min_length'        => $settings['email_password.min_length'] ?? 10,
                    'complexity'        => $settings['email_password.complexity'] ?? 3,
                    'require_uppercase' => $settings['email_password.require_uppercase'] ?? false,
                    'require_lowercase' => $settings['email_password.require_lowercase'] ?? false,
                    'require_number'    => $settings['email_password.require_number'] ?? false,
                    'require_special'   => $settings['email_password.require_special'] ?? false,
                ],
                'passkeys'    => ['enabled' => $settings['passkeys.enabled'] ?? true],
                'magic_links' => ['enabled' => $settings['magic_links.enabled'] ?? true],
                'sso'         => ['enabled' => $settings['sso.enabled'] ?? true],
                'mfa'         => [
                    'required'      => $settings['mfa.required'] ?? false,
                    'totp_enabled'  => $settings['mfa.totp.enabled'] ?? true,
                    'sms_enabled'   => $settings['mfa.sms.enabled'] ?? false,
                ],
            ],
            'session' => [
                'ttl_days'            => $settings['session.ttl_days'] ?? 7,
                'inactivity_minutes'  => $settings['session.inactivity_minutes'] ?? 60,
                'concurrent_limit'    => $settings['session.concurrent_limit'] ?? 5,
            ],
            'password' => [
                'reject_breached'  => $settings['password.reject_breached'] ?? true,
                'history_count'    => $settings['password.history_count'] ?? 5,
                'expiration_days'  => $settings['password.expiration_days'] ?? 90,
            ],
        ]);
    }

    /**
     * Update a login method toggle or setting value.
     */
    public function updateMethod(Request $request)
    {
        $request->validate([
            'key'      => 'nullable|string|max:100',
            'value'    => 'nullable',
            'settings' => 'nullable|array',
        ]);

        if ($request->filled('settings')) {
            foreach ($request->settings as $k => $v) {
                AuthSetting::set($k, $v);
                AuthAuditLog::log('auth.setting.changed', $request->user()?->id, [
                    'key'   => $k,
                    'value' => $v,
                ]);
            }
            return response()->json(['message' => 'Settings updated.']);
        }

        AuthSetting::set($request->key, $request->value);

        // Audit log the change
        AuthAuditLog::log('auth.setting.changed', $request->user()?->id, [
            'key'   => $request->key,
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
                    'enabled'      => $settings['feature.hosted_ui.enabled'] ?? true,
                    'idp_sso'      => $settings['feature.hosted_ui.idp_sso'] ?? true,
                    'redirect_uri' => config('app.url') . '/auth/callback',
                    'authkit_url'  => $settings['feature.hosted_ui.authkit_url'] ?? '',
                ],
                'sign_up' => [
                    'enabled' => $settings['feature.sign_up.enabled'] ?? true,
                ],
                'invitations' => [
                    'enabled'          => $settings['feature.invitations.enabled'] ?? true,
                    'expiry_days'      => (int) ($settings['feature.invitations.expiry_days'] ?? 7),
                ],
                'mfa' => [
                    'mode' => $settings['feature.mfa.mode'] ?? 'off', // off | optional | required
                ],
                'localization' => [
                    'enabled'           => $settings['feature.localization.enabled'] ?? true,
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
            'key'      => 'nullable|string|max:100',
            'value'    => 'nullable',
            'settings' => 'nullable|array',
        ]);

        if ($request->filled('settings')) {
            foreach ($request->settings as $k => $v) {
                AuthSetting::set($k, $v);
                AuthAuditLog::log('auth.feature.changed', $request->user()?->id, [
                    'key'   => $k,
                    'value' => $v,
                ]);
            }
            return response()->json(['message' => 'Feature settings updated.']);
        }

        AuthSetting::set($request->key, $request->value);
        AuthAuditLog::log('auth.feature.changed', $request->user()?->id, [
            'key'   => $request->key,
            'value' => $request->value,
        ]);

        return response()->json(['message' => 'Feature updated.']);
    }



    public function providers()
    {
        $providers = AuthOAuthProvider::orderBy('name')->get()->map(function ($p) {
            return [
                'id'           => $p->id,
                'name'         => $p->name,
                'slug'         => $p->slug,
                'is_enabled'   => (bool) $p->is_enabled,
                'use_demo'     => (bool) $p->use_demo_credentials,
                'has_custom'   => !empty($p->client_id),
                'client_id'    => $p->client_id,
                'scopes'       => $p->scopes,
                'redirect_uri' => url("/auth/oauth/{$p->slug}/callback"),
            ];
        });

        return response()->json(['providers' => $providers]);
    }

    public function updateProvider(Request $request, string $slug)
    {
        $request->validate([
            'is_enabled'        => 'sometimes|boolean',
            'use_demo'          => 'sometimes|boolean',
            'client_id'         => 'sometimes|nullable|string|max:500',
            'client_secret'     => 'sometimes|nullable|string|max:2000',
            'scopes'            => 'sometimes|nullable|string',
        ]);

        $provider = AuthOAuthProvider::where('slug', $slug)->firstOrFail();

        $data = [];

        if ($request->has('is_enabled')) $data['is_enabled'] = $request->boolean('is_enabled');
        if ($request->has('use_demo'))   $data['use_demo_credentials'] = $request->boolean('use_demo');
        if ($request->has('client_id'))  $data['client_id'] = $request->client_id;
        if ($request->has('scopes'))     $data['scopes'] = $request->scopes;

        // Encrypt client secret before storing
        if ($request->filled('client_secret')) {
            $data['client_secret'] = \Illuminate\Support\Facades\Crypt::encryptString($request->client_secret);
        }

        $provider->update($data);

        // Bust cache
        Cache::forget("oauth_provider.{$slug}");

        AuthAuditLog::log('auth.provider.toggled', $request->user()?->id, [
            'provider'   => $slug,
            'is_enabled' => $data['is_enabled'] ?? null,
        ]);

        return response()->json([
            'message'  => 'Provider updated.',
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
        $query = AuthSession::with(['user:id,name,email', 'device'])
            ->orderBy('last_activity_at', 'desc');

        // Filter
        if ($request->boolean('revoked')) {
            $query->where('is_revoked', true);
        } else {
            $query->where('is_revoked', false);
        }

        if ($request->filled('risk_level')) {
            match ($request->risk_level) {
                'high'   => $query->where('risk_score', '>', 60),
                'medium' => $query->whereBetween('risk_score', [21, 60]),
                'safe'   => $query->where('risk_score', '<=', 20),
                default  => null,
            };
        }

        $sessions = $query->paginate(20)->through(function ($s) {
            return [
                'id'              => $s->id,
                'user'            => $s->user,
                'device'          => $s->device,
                'ip_address'      => $s->ip_address,
                'geolocation'     => $s->geolocation,
                'risk_score'      => $s->risk_score,
                'risk_level'      => $s->risk_score > 60 ? 'high' : ($s->risk_score > 20 ? 'medium' : 'safe'),
                'is_revoked'      => $s->is_revoked,
                'last_activity_at'=> $s->last_activity_at?->toISOString(),
                'expires_at'      => $s->expires_at?->toISOString(),
                'created_at'      => $s->created_at?->toISOString(),
            ];
        });

        $stats = Cache::remember('auth.session.stats', 60, function () {
            return [
                'total_active'  => AuthSession::where('is_revoked', false)->count(),
                'total_revoked' => AuthSession::where('is_revoked', true)->count(),
                'high_risk'     => AuthSession::where('is_revoked', false)->where('risk_score', '>', 60)->count(),
            ];
        });

        return response()->json(['sessions' => $sessions, 'stats' => $stats]);
    }

    public function revokeSession(Request $request, AuthSession $session)
    {
        $session->update(['is_revoked' => true]);

        AuthAuditLog::log('auth.session.revoked', $request->user()?->id, [
            'session_id'      => $session->id,
            'target_user_id'  => $session->user_id,
        ], 'warning');

        Cache::forget('auth.session.stats');
        Cache::flush();

        return response()->json(['message' => 'Session revoked.']);
    }

    public function revokeAllSessions(Request $request)
    {
        $count = AuthSession::where('is_revoked', false)->update(['is_revoked' => true]);

        AuthAuditLog::log('auth.session.revoke_all', $request->user()?->id, [
            'count' => $count,
        ], 'warning');

        Cache::forget('auth.session.stats');
        Cache::flush();

        return response()->json(['message' => "{$count} sessions revoked."]);
    }

    public function sessionsConfig()
    {
        $settings = AuthSetting::getAllTyped();

        $users = User::take(5)->get()->map(function($user) {
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

    public function previewJwt(Request $request)
    {
        $request->validate([
            'jwtCode' => 'required|string',
            'userId' => 'required|exists:users,id',
        ]);

        $user = User::find($request->userId);
        $module = Module::first();

        $firstName = explode(' ', $user->name)[0] ?? 'Someone';
        $lastName = count(explode(' ', $user->name)) > 1 ? implode(' ', array_slice(explode(' ', $user->name), 1)) : 'Unknown';

        $context = [
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'metadata' => [
                    'language' => 'id-ID',
                ]
            ],
            'organization' => [
                'name' => $module?->name ?? 'FMIKOM Staging Org',
                'code' => $module?->code ?? 'fmikom-staging',
                'metadata' => [
                    'tier' => 'enterprise',
                ],
                'domains' => [
                    ['domain' => 'fmikom.org']
                ]
            ]
        ];

        $template = $request->jwtCode;

        // Compile template
        // Replace liquid-like expressions: {{ path.to.var || 'default' }}
        $compiled = preg_replace_callback('/\{\{\s*([^\}]+)\s*\}\}/', function($matches) use ($context) {
            $expression = trim($matches[1]);
            
            // Handle fallback operator "||"
            $parts = explode('||', $expression);
            
            foreach ($parts as $part) {
                $part = trim($part);
                
                // String literal check
                if (preg_match('/^[\'"](.*)[\'"]$/', $part, $strMatch)) {
                    return $strMatch[1];
                }
                
                // Resolve path from context
                $val = data_get($context, $part);
                if ($val !== null && $val !== '') {
                    return is_array($val) ? json_encode($val) : (string)$val;
                }
            }
            
            return '';
        }, $template);

        // Format beautifully if valid JSON
        $decoded = json_decode($compiled, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $compiled = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        return response()->json([
            'compiled' => $compiled,
            'user' => $context['user'],
            'organization' => $context['organization'],
        ]);
    }

    // =========================================================================
    // MFA PLATFORM STATUS
    // =========================================================================

    public function mfaStatus()
    {
        $totalUsers = User::count();
        $mfaUsers   = AuthMfa::where('is_active', true)->distinct('user_id')->count('user_id');

        return response()->json([
            'total_users'   => $totalUsers,
            'mfa_users'     => $mfaUsers,
            'adoption_rate' => $totalUsers > 0 ? round(($mfaUsers / $totalUsers) * 100, 1) : 0,
            'required'      => AuthSetting::get('mfa.required', false),
            'totp_enabled'  => AuthSetting::get('mfa.totp.enabled', true),
            'sms_enabled'   => AuthSetting::get('mfa.sms.enabled', false),
        ]);
    }

    // =========================================================================
    // AUDIT LOGS
    // =========================================================================

    public function auditLogs(Request $request)
    {
        $query = AuthAuditLog::with('user:id,name,email')
            ->orderByDesc('occurred_at');

        if ($request->filled('event'))    $query->where('event', 'like', '%' . $request->event . '%');
        if ($request->filled('severity')) $query->where('severity', $request->severity);
        if ($request->filled('user_id'))  $query->where('user_id', $request->user_id);
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
            'key'   => 'required|string|starts_with:password.',
            'value' => 'required',
        ]);

        AuthSetting::set($request->key, $request->value);

        AuthAuditLog::log('auth.setting.changed', $request->user()?->id, [
            'key'   => $request->key,
            'value' => $request->value,
        ]);

        return response()->json(['message' => 'Password policy updated.']);
    }

    public function deleteFailedLogin(AuthLoginAttempt $attempt)
    {
        $attempt->delete();
        Cache::flush();
        return response()->json(['message' => 'Failed login record cleared.']);
    }

    public function deleteUser(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Tidak dapat menghapus akun sendiri.');
        abort_if($user->user_type === 'super_admin', 403, 'Akun Super Admin dilindungi. Silakan ubah tipe/role user ini terlebih dahulu jika ingin menghapusnya.');

        $user->delete();
        Cache::flush();
        return response()->json(['message' => 'User deleted successfully.']);
    }

    public function clearAnalytics(Request $request)
    {
        // Delete all active sessions and login attempts
        AuthSession::query()->delete();
        AuthLoginAttempt::query()->delete();
        Cache::flush();
        return response()->json(['success' => true, 'message' => 'Analytics data reset successfully.']);
    }
}
