<?php

use App\Models\User;
use App\Models\Audit\AuditLog;
use App\Models\Audit\AuditSecurityIncident;
use App\Models\Audit\AuditApiRequest;

test('unauthorized users cannot clear audit logs', function () {
    $nonAdmin = User::factory()->create(['user_type' => 'mahasiswa']);
    $this->actingAs($nonAdmin);

    $response = $this->post(route('workos.audit-logs.clear'));

    $response->assertRedirect(route('dashboard'));
});

test('super admin can clear audit logs', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    
    // Create mock audit data
    $log = AuditLog::create([
        'event_type' => 'test.event',
        'severity' => 'info',
        'actor_id' => $admin->id,
        'ip_address' => '127.0.0.1',
    ]);

    $incident = AuditSecurityIncident::create([
        'audit_log_id' => $log->id,
        'incident_type' => 'brute_force',
        'user_id' => $admin->id,
        'ip_address' => '127.0.0.1',
        'severity' => 'high',
        'details' => [],
        'mitigation_status' => 'auto_blocked',
    ]);

    $apiRequest = AuditApiRequest::create([
        'user_id' => $admin->id,
        'endpoint' => '/api/test',
        'method' => 'GET',
        'status_code' => 200,
        'response_time_ms' => 12,
        'ip_address' => '127.0.0.1',
    ]);

    $this->actingAs($admin);

    $response = $this->post(route('workos.audit-logs.clear'));

    $response->assertRedirect();
    
    // Check tables are cleared
    $this->assertDatabaseMissing('audit_security_incidents', ['id' => $incident->id]);
    $this->assertDatabaseMissing('audit_logs', ['id' => $log->id]);
    $this->assertDatabaseMissing('audit_api_requests', ['id' => $apiRequest->id]);
});
