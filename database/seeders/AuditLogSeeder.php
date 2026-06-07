<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Audit\AuditLog;
use App\Models\User;
use App\Models\Module;
use App\Models\Audit\AuditSecurityIncident;

class AuditLogSeeder extends Seeder
{
    public function run()
    {
        $user1 = User::where('email', 'muchlisinmaruf@gmail.com')->first();
        $user2 = User::where('email', 'andimahasiswa@example.com')->first();
        $user3 = User::where('email', 'alumni@example.com')->first();

        $module1 = Module::where('code', 'FAST')->first();
        $module2 = Module::where('code', 'WIMS')->first();
        $module3 = Module::where('code', 'PAGI')->first();
        $module4 = Module::where('code', 'TRACE')->first();

        $logs = [
            [
                'event_type' => 'user.signed_in',
                'severity' => 'info',
                'actor_id' => $user1?->id,
                'organization_id' => $module1?->id,
                'target_type' => 'App\Models\User',
                'target_id' => $user1?->id,
                'ip_address' => '182.253.140.23',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'request_method' => 'POST',
                'request_path' => 'login',
                'response_status' => 200,
                'correlation_id' => 'corr_' . bin2hex(random_bytes(8)),
                'metadata' => ['device' => 'Safari / macOS'],
                'created_at' => now()->subMinutes(15),
            ],
            [
                'event_type' => 'organization.updated',
                'severity' => 'info',
                'actor_id' => $user1?->id,
                'organization_id' => $module1?->id,
                'target_type' => 'App\Models\Module',
                'target_id' => $module1?->id,
                'ip_address' => '182.253.140.10',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'request_method' => 'PATCH',
                'request_path' => 'workos/modules/' . ($module1?->id ?? 1),
                'response_status' => 200,
                'correlation_id' => 'corr_' . bin2hex(random_bytes(8)),
                'metadata' => ['updated_fields' => ['name' => 'Fmikom Academic System and Tracking']],
                'created_at' => now()->subHours(2),
            ],
            [
                'event_type' => 'role.assigned',
                'severity' => 'info',
                'actor_id' => $user1?->id,
                'organization_id' => $module2?->id,
                'target_type' => 'App\Models\User',
                'target_id' => $user2?->id,
                'ip_address' => '182.253.140.10',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'request_method' => 'POST',
                'request_path' => 'workos/roles/assign',
                'response_status' => 200,
                'correlation_id' => 'corr_' . bin2hex(random_bytes(8)),
                'metadata' => ['role' => 'mahasiswa', 'user' => $user2?->email],
                'created_at' => now()->subHours(5),
            ],
            [
                'event_type' => 'domain.verified',
                'severity' => 'info',
                'actor_id' => $user1?->id,
                'organization_id' => $module3?->id,
                'target_type' => 'App\Models\Module',
                'target_id' => $module3?->id,
                'ip_address' => '110.138.80.45',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'request_method' => 'POST',
                'request_path' => 'workos/domains/verify',
                'response_status' => 200,
                'correlation_id' => 'corr_' . bin2hex(random_bytes(8)),
                'metadata' => ['domain' => 'fmikom.org'],
                'created_at' => now()->subDays(1),
            ],
            [
                'event_type' => 'member.invited',
                'severity' => 'info',
                'actor_id' => $user1?->id,
                'organization_id' => $module1?->id,
                'target_type' => 'App\Models\User',
                'target_id' => $user3?->id,
                'ip_address' => '182.253.140.23',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'request_method' => 'POST',
                'request_path' => 'workos/invites',
                'response_status' => 200,
                'correlation_id' => 'corr_' . bin2hex(random_bytes(8)),
                'metadata' => ['invitee_email' => 'dosen@fmikom.org'],
                'created_at' => now()->subDays(2),
            ],
            [
                'event_type' => 'api_key.created',
                'severity' => 'info',
                'actor_id' => $user1?->id,
                'organization_id' => $module4?->id,
                'target_type' => 'App\Models\Module',
                'target_id' => $module4?->id,
                'ip_address' => '110.138.80.45',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                'request_method' => 'POST',
                'request_path' => 'workos/api_keys',
                'response_status' => 201,
                'correlation_id' => 'corr_' . bin2hex(random_bytes(8)),
                'metadata' => ['key_name' => 'Production API Key'],
                'created_at' => now()->subDays(3),
            ],
            [
                'event_type' => 'sso.connection.activated',
                'severity' => 'warning',
                'actor_id' => $user1?->id,
                'organization_id' => $module2?->id,
                'target_type' => 'App\Models\Module',
                'target_id' => $module2?->id,
                'ip_address' => '182.253.140.10',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
                'request_method' => 'POST',
                'request_path' => 'workos/sso/activate',
                'response_status' => 200,
                'correlation_id' => 'corr_' . bin2hex(random_bytes(8)),
                'metadata' => ['provider' => 'Google OAuth Connection'],
                'created_at' => now()->subDays(4),
            ],
        ];

        foreach ($logs as $log) {
            AuditLog::create($log);
        }

        // Also seed a couple of security incidents for AuditLogs Index statistics
        $secLog = AuditLog::create([
            'event_type' => 'security.incident',
            'severity' => 'high',
            'actor_id' => $user2?->id,
            'organization_id' => $module1?->id,
            'target_type' => 'App\Models\User',
            'target_id' => $user2?->id,
            'ip_address' => '192.168.1.100',
            'user_agent' => 'Hydra Attack Tool',
            'request_method' => 'POST',
            'request_path' => 'login',
            'response_status' => 401,
            'correlation_id' => 'corr_' . bin2hex(random_bytes(8)),
            'metadata' => ['incident_type' => 'brute_force', 'details' => ['attempts' => 12]],
        ]);

        AuditSecurityIncident::create([
            'audit_log_id' => $secLog->id,
            'incident_type' => 'brute_force',
            'user_id' => $user2?->id,
            'ip_address' => '192.168.1.100',
            'severity' => 'high',
            'details' => ['attempts' => 12],
            'mitigation_status' => 'auto_blocked',
        ]);
    }
}
