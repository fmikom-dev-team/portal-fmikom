<?php

namespace App\Modules\WorkOs\Services;

use App\Models\Audit\AuditApiRequest;
use App\Models\Audit\AuditLog;
use App\Models\Audit\AuditSecurityIncident;
use App\Models\Auth\AuthLoginAttempt;
use App\Models\Auth\AuthMagicLink;
use App\Models\Auth\AuthMfa;
use App\Models\Auth\AuthSsoConnection;
use App\Models\Module;
use App\Models\Radar\RadarBlockedItem;
use App\Models\Radar\RadarDetection;
use App\Models\Radar\RadarProtection;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardStatsService
{
    private const ZERO_PERCENT = '0.00%';

    public function getStats(): array
    {
        $weeklyLogins = $this->getWeeklyLoginsData();
        $hourlyLogins = $this->getHourlyLoginsData();
        $roleLogins = $this->getRoleLoginsData();
        $userTypes = $this->getUserDistributionData();
        $prodiDistribution = $this->getProdiDistributionData();
        $securityCenter = $this->getSecurityMetricsData();
        $activities = $this->getRecentActivitiesData();
        $auditCompliance = $this->getAuditComplianceData();
        $systemHealth = $this->getSystemHealthData();

        $loginHariIni = AuthLoginAttempt::query()->where('is_successful', '=', true, 'and')->whereDate('created_at', today())->count('*');
        $loginGagalHariIni = AuthLoginAttempt::query()->where('is_successful', '=', false, 'and')->whereDate('created_at', today())->count('*');
        $organisasiTerhubung = Module::query()->where('is_active', '=', true, 'and')->count('*');

        return [
            'total_users' => User::query()->count('*'),
            'active_users' => User::query()->where('is_active', '=', true, 'and')->count('*'),
            'pending_users' => User::query()->where('status_approval', '=', 'pending', 'and')->count('*'),
            'total_roles' => Role::query()->count('*'),
            'total_modules' => Module::query()->where('is_active', '=', true, 'and')->count('*'),

            // Hero stats
            'login_hari_ini' => $loginHariIni,
            'login_gagal_hari_ini' => $loginGagalHariIni,
            'organisasi_terhubung' => $organisasiTerhubung,

            // Analytics
            'weekly_logins' => $weeklyLogins,
            'hourly_logins' => $hourlyLogins,
            'role_logins' => $roleLogins,
            'prodi_distribution' => $prodiDistribution,
            'user_types' => $userTypes,

            // Security Center
            'security_center' => $securityCenter,

            // Activities
            'recent_activities' => $activities,
            'audit_compliance' => $auditCompliance,

            // System Health details
            'system_health' => $systemHealth,
        ];
    }

    public function getRadarConfig(): array
    {
        return RadarProtection::all()->toArray();
    }

    public function getRadarStats(): array
    {
        $total = RadarDetection::query()->count('*');
        $allowed = RadarDetection::query()->where('action_taken', '=', 'Allowed', 'and')->count('*');
        $challenged = RadarDetection::query()->where('action_taken', '=', 'Challenged', 'and')->count('*');
        $blocked = RadarDetection::query()->where('action_taken', '=', 'Blocked', 'and')->count('*');

        // Breakdown counts (all time)
        $breakdown = RadarDetection::query()->select('detection_type', DB::raw('count(*) as count'))
            ->groupBy('detection_type')
            ->pluck('count', 'detection_type')
            ->toArray();

        return [
            'total' => $total,
            'allowed' => $allowed,
            'challenged' => $challenged,
            'blocked' => $blocked,
            'breakdown' => $breakdown,
        ];
    }

    public function getRadarDetections(): array
    {
        return RadarDetection::query()->with(['protection', 'device'])
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($d) {
                return [
                    'id' => $d->id,
                    'type' => $d->detection_type,
                    'severity' => $d->severity,
                    'risk_score' => $d->risk_score,
                    'action' => $d->action_taken,
                    'ip' => $d->ip_address,
                    'device' => $d->device ? $d->device->os.' '.$d->device->browser : 'Unknown',
                    'country' => $d->device ? $d->device->country : 'Unknown',
                    'city' => $d->device ? $d->device->city : 'Unknown',
                    'metadata' => $d->metadata,
                    'created_at' => $d->created_at->format('M d, g:i A'),
                    'created_at_human' => $d->created_at->diffForHumans(),
                    'created_at_iso' => $d->created_at->toISOString(),
                ];
            })->toArray();
    }

    public function getAuditStats(): array
    {
        return [
            'total_events' => AuditLog::query()->count('*'),
            'active_users' => AuditLog::query()->whereNotNull('actor_id', 'and')->distinct()->count('actor_id'),
            'security_incidents' => AuditSecurityIncident::query()->count('*'),
            'failed_actions' => AuditApiRequest::query()->where('status_code', '>=', 400, 'and')->count('*'),
        ];
    }

    public function getAuditRecentEvents(): array
    {
        return AuditLog::query()->with('actor:id,name,email')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    private function getWeeklyLoginsData(): array
    {
        $weeklyLogins = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateObj = now()->subDays($i);
            $date = $dateObj->format('Y-m-d');
            $dateLabel = $dateObj->isoFormat('ddd');

            $success = AuthLoginAttempt::query()->where('is_successful', '=', true, 'and')->whereDate('created_at', $date)->count('*');
            $failed = AuthLoginAttempt::query()->where('is_successful', '=', false, 'and')->whereDate('created_at', $date)->count('*');

            $weeklyLogins[] = [
                'date' => $date,
                'label' => $dateLabel,
                'success' => $success,
                'failed' => $failed,
            ];
        }

        return $weeklyLogins;
    }

    private function getHourlyLoginsData(): array
    {
        $hourlyLogins = [];
        for ($h = 0; $h < 24; $h++) {
            $success = AuthLoginAttempt::query()->where('is_successful', '=', true, 'and')
                ->whereDate('created_at', today())
                ->whereRaw('HOUR(created_at) = ?', [$h])
                ->count('*');

            $hourlyLogins[] = [
                'hour' => sprintf('%02d:00', $h),
                'success' => $success,
            ];
        }

        return $hourlyLogins;
    }

    private function getRoleLoginsData(): array
    {
        return AuthLoginAttempt::query()->where('is_successful', '=', true, 'and')
            ->join('users', 'auth_login_attempts.email', '=', 'users.email')
            ->selectRaw('users.user_type as role, count(*) as count')
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role')
            ->toArray();
    }

    private function getUserDistributionData(): array
    {
        return User::query()->selectRaw('user_type, count(*) as count')
            ->groupBy('user_type')
            ->get()
            ->pluck('count', 'user_type')
            ->toArray();
    }

    private function getProdiDistributionData(): array
    {
        return User::query()->join('program_studis', 'users.program_studi_id', '=', 'program_studis.id')
            ->selectRaw('program_studis.nama as prodi, count(*) as count')
            ->groupBy('prodi')
            ->get()
            ->pluck('count', 'prodi')
            ->toArray();
    }

    private function getSecurityMetricsData(): array
    {
        $failedLoginsToday = AuthLoginAttempt::query()->where('is_successful', '=', false, 'and')->whereDate('created_at', today())->count('*');
        $blockedIps = RadarBlockedItem::query()->where('type', '=', 'IP', 'and')->count('*');

        $mfaCount = AuthMfa::query()->where('is_active', '=', true, 'and')->count('*');
        $userCount = max(User::query()->count('*'), 1);
        $mfaAdoption = (int) (($mfaCount / $userCount) * 100);

        $pwdResets = AuthMagicLink::query()->where('is_used', '=', false, 'and')->where('expires_at', '>', now())->count('*');

        return [
            'failed_login_today' => $failedLoginsToday,
            'blocked_ips' => $blockedIps,
            'mfa_adoption_rate' => $mfaAdoption,
            'password_reset_request' => $pwdResets,
        ];
    }

    private function getRecentActivitiesData(): array
    {
        $recentAuditLogs = AuditLog::with('actor:id,name,email,user_type')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $activities = [];
        foreach ($recentAuditLogs as $log) {
            /** @var User|null $actor */
            $actor = $log->actor;
            $actorName = $actor ? $actor->name : 'System';
            $actorType = $actor ? $actor->user_type : 'system';
            $time = $log->created_at->toISOString();

            $desc = '';
            switch ($log->event_type) {
                case 'user.signed_in':
                    $desc = "{$actorName} ({$actorType}) login ke portal";
                    break;
                case 'organization.updated':
                    $desc = 'Admin memperbarui informasi organisasi';
                    break;
                case 'role.assigned':
                    $desc = 'Role baru ditetapkan ke user';
                    break;
                case 'domain.verified':
                    $desc = 'Verifikasi domain berhasil dilakukan';
                    break;
                case 'member.invited':
                    $desc = 'Undangan dikirim ke member baru';
                    break;
                case 'api_key.created':
                    $desc = 'API Key baru berhasil dibuat';
                    break;
                case 'sso.connection.activated':
                    $desc = 'Koneksi SSO diaktifkan';
                    break;
                case 'security.incident':
                    $desc = 'Insiden keamanan terdeteksi dan diblokir';
                    break;
                default:
                    $desc = 'Aktivitas audit log: '.str_replace(['.', '_'], ' ', $log->event_type);
            }

            $activities[] = [
                'description' => $desc,
                'time' => $time,
                'type' => $log->event_type,
            ];
        }

        return $activities;
    }

    private function getAuditComplianceData(): array
    {
        return [
            'login_events' => AuthLoginAttempt::query()->count('*'),
            'permission_changes' => AuditLog::query()->where('event_type', 'like', '%permission%', 'and')->count('*'),
            'role_updates' => AuditLog::query()->where('event_type', 'like', '%role%', 'and')->count('*'),
            'security_alerts' => AuditSecurityIncident::query()->count('*'),
        ];
    }

    private function getSystemHealthData(): array
    {
        try {
            DB::connection()->getPdo();
            $dbStatus = 'online';
            $dbUptime = '100.00%';
        } catch (\Exception $e) {
            $dbStatus = 'offline';
            $dbUptime = self::ZERO_PERCENT;
        }

        $mailConfig = config('mail.mailers.smtp');
        $mailStatus = (! empty($mailConfig['host']) && ! empty($mailConfig['username'])) ? 'online' : 'offline';
        $mailUptime = $mailStatus === 'online' ? '99.98%' : self::ZERO_PERCENT;

        $ssoCount = AuthSsoConnection::query()->count('*');
        $ssoStatus = $ssoCount > 0 ? 'online' : 'offline';
        $ssoUptime = $ssoStatus === 'online' ? '99.97%' : self::ZERO_PERCENT;

        $directoryStatus = Module::query()->where('is_active', '=', true, 'and')->exists() ? 'online' : 'offline';
        $directoryUptime = $directoryStatus === 'online' ? '99.95%' : self::ZERO_PERCENT;

        $sessionDriver = config('session.driver');
        $authStatus = ! empty($sessionDriver) ? 'online' : 'offline';
        $authUptime = $authStatus === 'online' ? '99.99%' : self::ZERO_PERCENT;

        // Calculate average uptime of online services
        $onlineServicesCount = 0;
        $totalUptimeSum = 0;
        $serviceList = [
            ['status' => $authStatus, 'uptime' => 99.99],
            ['status' => $ssoStatus, 'uptime' => 99.97],
            ['status' => $directoryStatus, 'uptime' => 99.95],
            ['status' => $mailStatus, 'uptime' => 98.40],
            ['status' => $dbStatus, 'uptime' => 100.00],
        ];

        foreach ($serviceList as $srv) {
            if ($srv['status'] === 'online') {
                $onlineServicesCount++;
                $totalUptimeSum += $srv['uptime'];
            }
        }
        $avgUptimeText = $onlineServicesCount > 0 ? number_format($totalUptimeSum / $onlineServicesCount, 2).'%' : self::ZERO_PERCENT;

        return [
            'avg_uptime' => $avgUptimeText,
            'services' => [
                ['name' => 'Auth Service', 'status' => $authStatus, 'uptime' => $authUptime, 'description' => 'Autentikasi & manajemen sesi pengguna'],
                ['name' => 'SSO Gateway', 'status' => $ssoStatus, 'uptime' => $ssoUptime, 'description' => 'Integrasi IdP (SAML, OIDC, OAuth)'],
                ['name' => 'Directory Sync', 'status' => $directoryStatus, 'uptime' => $directoryUptime, 'description' => 'Sinkronisasi SCIM & LDAP aktif'],
                ['name' => 'Email Service', 'status' => $mailStatus, 'uptime' => $mailUptime, 'description' => 'Pengiriman email undangan & OTP'],
                ['name' => 'Database Cluster', 'status' => $dbStatus, 'uptime' => $dbUptime, 'description' => 'Database utama & replika sinkron'],
            ],
        ];
    }
}
