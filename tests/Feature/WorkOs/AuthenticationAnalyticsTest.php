<?php

use App\Models\User;
use App\Models\Auth\AuthSession;
use App\Models\Auth\AuthLoginAttempt;

test('unauthorized users cannot clear authentication analytics', function () {
    $nonAdmin = User::factory()->create(['user_type' => 'mahasiswa']);
    $this->actingAs($nonAdmin);

    $response = $this->post(route('workos.auth-platform.analytics.clear'));

    $response->assertRedirect(route('dashboard'));
});

test('super admin can clear authentication analytics', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    
    // Create mock analytics data
    $session = AuthSession::create([
        'user_id' => $admin->id,
        'session_token' => 'session-to-clear',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Browser',
        'is_revoked' => false,
        'expires_at' => now()->addDays(7),
        'last_activity_at' => now(),
    ]);

    $attempt = AuthLoginAttempt::create([
        'email' => $admin->email,
        'ip_address' => '127.0.0.1',
        'is_successful' => false,
        'failure_reason' => 'Invalid credentials',
    ]);

    $this->actingAs($admin);

    $response = $this->post(route('workos.auth-platform.analytics.clear'));

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Analytics data reset successfully.',
    ]);

    // Verify database tables are cleared
    $this->assertDatabaseMissing('auth_sessions', ['id' => $session->id]);
    $this->assertDatabaseMissing('auth_login_attempts', ['id' => $attempt->id]);
});
