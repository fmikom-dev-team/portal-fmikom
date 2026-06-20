<?php

use App\Models\Audit\AuditApiRequest;
use App\Models\Audit\AuditLog;
use App\Models\Audit\AuditSecurityIncident;
use App\Models\Module;
use App\Models\Role;
use App\Models\User;
use App\Models\UserModuleRole;
use Illuminate\Support\Facades\DB;

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

test('selecting a module generates module.accessed audit log', function () {
    $user = User::factory()->create();
    $module = Module::firstOrCreate(['code' => 'FAST'], [
        'name' => 'FAST Portal',
        'is_active' => true,
    ]);
    $role = Role::firstOrCreate(['slug' => 'mahasiswa'], [
        'nama' => 'Mahasiswa',
    ]);

    // Set up assignment
    UserModuleRole::create([
        'user_id' => $user->id,
        'module_id' => $module->id,
        'role_id' => $role->id,
        'is_active' => true,
    ]);

    // Map role to module
    DB::table('module_roles')->insertOrIgnore([
        'module_id' => $module->id,
        'role_id' => $role->id,
    ]);

    $response = $this->actingAs($user)
        ->post(route('module.select'), [
            'module_code' => 'FAST',
            'role_slug' => 'mahasiswa',
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('audit_logs', [
        'event_type' => 'module.accessed',
        'actor_id' => $user->id,
        'organization_id' => $module->id,
    ]);
});

test('switching a role generates role.switched audit log', function () {
    $user = User::factory()->create();
    $module = Module::firstOrCreate(['code' => 'FAST'], [
        'name' => 'FAST Portal',
        'is_active' => true,
    ]);
    $role1 = Role::firstOrCreate(['slug' => 'mahasiswa'], [
        'nama' => 'Mahasiswa',
    ]);
    $role2 = Role::firstOrCreate(['slug' => 'admin'], [
        'nama' => 'Admin',
    ]);

    // Set up assignments
    UserModuleRole::create([
        'user_id' => $user->id,
        'module_id' => $module->id,
        'role_id' => $role1->id,
        'is_active' => true,
    ]);
    UserModuleRole::create([
        'user_id' => $user->id,
        'module_id' => $module->id,
        'role_id' => $role2->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->withSession([
            'active_module' => 'FAST',
            'active_role' => 'mahasiswa',
        ])
        ->post(route('portal.switch-role'), [
            'role_slug' => 'admin',
        ]);

    $response->assertOk();

    $this->assertDatabaseHas('audit_logs', [
        'event_type' => 'role.switched',
        'actor_id' => $user->id,
        'organization_id' => $module->id,
    ]);
});
