<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Auth\AuthLoginAttempt;
use App\Models\Auth\AuthMfa;
use App\Models\Auth\AuthPasskey;
use App\Models\Auth\AuthSession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AuthAnalyticsController extends Controller
{
    /**
     * Display Auth Analytics
     */
    public function analytics(Request $request)
    {
        $days = min((int) $request->get('days', 7), 365);
        $interval = $request->get('interval', 'daily'); // daily, weekly
        if (! in_array($interval, ['daily', 'weekly'], true)) {
            $interval = 'daily';
        }
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        // 1. User Stats
        $totalUsers = User::query()->count('*');
        $newUsers = User::query()->where('created_at', '>=', $startDate)->count('*');

        // 2. Active sessions in period
        $activeSessions = AuthSession::query()->where('last_activity_at', '>=', $startDate)
            ->where('is_revoked', '=', false)
            ->distinct()
            ->count('user_id');

        // 3. Login Attempts
        $attempts = AuthLoginAttempt::query()->where('created_at', '>=', $startDate)->get();
        $totalAttempts = $attempts->count();
        $successAttempts = $attempts->where('is_successful', true)->count();
        $failedAttempts = $totalAttempts - $successAttempts;
        $successRate = $totalAttempts > 0 ? round(($successAttempts / $totalAttempts) * 100, 1) : 0;

        // 4. MFA Adoption
        $mfaUsers = AuthMfa::query()->where('is_active', '=', true)->distinct()->count('user_id');
        $mfaAdoption = $totalUsers > 0 ? round(($mfaUsers / $totalUsers) * 100, 1) : 0;

        // 5. Passkeys Registered
        $passkeyUsers = AuthPasskey::query()->distinct()->count('user_id');

        // 6. Provider Distribution
        $providerStats = AuthLoginAttempt::query()->where('created_at', '>=', $startDate, 'and')
            ->whereNotNull('provider', 'and')
            ->select(['provider', DB::raw('count(*) as count')])
            ->groupBy('provider')
            ->pluck('count', 'provider')
            ->toArray();

        // 7. Daily/Weekly Trends for chart
        $groupFormat = $interval === 'weekly' ? '%Y-%u' : '%Y-%m-%d';
        $trends = AuthLoginAttempt::query()->select([
            DB::raw("DATE_FORMAT(created_at, '{$groupFormat}') as period"),
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN is_successful = 1 THEN 1 ELSE 0 END) as successful'),
            DB::raw('SUM(CASE WHEN is_successful = 0 THEN 1 ELSE 0 END) as failed'),
        ])
            ->where('created_at', '>=', $startDate)
            ->groupBy('period')
            ->orderBy('period', 'asc')
            ->get();

        // 8. Risk distribution of active sessions
        $riskDistribution = [
            'safe' => AuthSession::query()->where('is_revoked', '=', false)->where('risk_score', '<=', 20)->count('*'),
            'medium' => AuthSession::query()->where('is_revoked', '=', false)->where('risk_score', '>=', 21)->where('risk_score', '<=', 60)->count('*'),
            'high' => AuthSession::query()->where('is_revoked', '=', false)->where('risk_score', '>', 60)->count('*'),
        ];

        // 9. Real-time detailed lists for interactive drill-down
        $activeSessionsList = AuthSession::with(['user:id,name,email', 'device'])
            ->where('last_activity_at', '>=', $startDate)
            ->where('is_revoked', '=', false)
            ->orderByDesc('last_activity_at')
            ->take(10)
            ->get()
            ->map(function ($s) {
                $riskLevel = 'safe';
                if ($s->risk_score > 60) {
                    $riskLevel = 'high';
                } elseif ($s->risk_score > 20) {
                    $riskLevel = 'medium';
                }

                return [
                    'id' => $s->id,
                    'user_name' => $s->user?->name ?? 'Unknown',
                    'user_email' => $s->user?->email ?? '-',
                    'ip_address' => $s->ip_address,
                    'device' => $s->device?->browser ?? 'Unknown',
                    'risk_level' => $riskLevel,
                    'last_active' => $s->last_activity_at?->diffForHumans() ?? '-',
                ];
            });

        $newUsersList = User::query()->where('created_at', '>=', $startDate)
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'user_type' => $u->user_type,
                    'created_at' => $u->created_at?->diffForHumans() ?? '-',
                ];
            });

        $failedLoginsList = AuthLoginAttempt::query()->where('created_at', '>=', $startDate)
            ->where('is_successful', '=', false)
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'email' => $attempt->email ?? 'Unknown',
                    'ip_address' => $attempt->ip_address ?? '-',
                    'provider' => $attempt->provider ?? 'Email/Password',
                    'reason' => $attempt->failure_reason ?? 'Invalid credentials',
                    'time' => $attempt->created_at?->diffForHumans() ?? '-',
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

    /**
     * Delete failed login attempt
     */
    public function deleteFailedLogin(AuthLoginAttempt $attempt)
    {
        AuthLoginAttempt::destroy($attempt->id);
        Cache::flush();

        return response()->json(['message' => 'Failed login record cleared.']);
    }

    /**
     * Delete user account
     */
    public function deleteUser(User $user)
    {
        abort_if($user->id === Auth::id(), 403, 'Tidak dapat menghapus akun sendiri.');
        abort_if($user->user_type === 'super_admin', 403, 'Akun Super Admin dilindungi. Silakan ubah tipe/role user ini terlebih dahulu jika ingin menghapusnya.');

        User::destroy($user->id);
        Cache::flush();

        return response()->json(['message' => 'User deleted successfully.']);
    }

    /**
     * Clear all analytics data
     */
    public function clearAnalytics(Request $request)
    {
        AuthSession::query()->delete();
        AuthLoginAttempt::query()->delete();
        Cache::flush();

        return response()->json(['success' => true, 'message' => 'Analytics data reset successfully.']);
    }
}
