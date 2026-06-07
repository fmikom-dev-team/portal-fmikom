<?php

use App\Models\Auth\AuthOAuthCredential;
use App\Models\Auth\AuthOAuthProvider;
use App\Models\Auth\AuthSession;
use App\Models\Module;
use App\Models\ProgramStudi;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    // Ensure we have a google oauth provider in the database
    $this->provider = AuthOAuthProvider::firstOrCreate(
        ['slug' => 'google'],
        ['name' => 'Google', 'client_id' => 'mock-client-id', 'client_secret' => 'mock-client-secret', 'is_active' => true]
    );
});

test('unauthorized users cannot update user details', function () {
    $admin = User::factory()->create(['user_type' => 'mahasiswa']); // not super admin
    $user = User::factory()->create();

    $this->actingAs($admin);

    $response = $this->patch(route('workos.users.update', $user), [
        'name' => 'Updated Name',
        'email' => 'updated-email@example.com',
    ]);

    // CheckRole will redirect them back to dashboard
    $response->assertRedirect(route('dashboard'));

    // Check that DB was not updated
    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
        'email' => 'updated-email@example.com',
    ]);
});

test('admin can update user details including email', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']); // acts as super admin
    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);

    $this->actingAs($admin);

    $response = $this->patch(route('workos.users.update', $user), [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'user_type' => 'mahasiswa',
        'is_active' => true,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

test('admin email update validation prevents duplicates', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $otherUser = User::factory()->create(['email' => 'taken@example.com']);
    $user = User::factory()->create(['email' => 'user@example.com']);

    $this->actingAs($admin);

    $response = $this->patch(route('workos.users.update', $user), [
        'name' => 'Updated Name',
        'email' => 'taken@example.com', // already taken
    ]);

    $response->assertSessionHasErrors(['email']);
    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'user@example.com', // unchanged
    ]);
});

test('unauthorized users cannot disconnect oauth credential', function () {
    $regularUser = User::factory()->create(['user_type' => 'mahasiswa']);
    $user = User::factory()->create();

    $credential = AuthOAuthCredential::create([
        'user_id' => $user->id,
        'provider_id' => $this->provider->id,
        'external_id' => 'google-external-123',
        'email' => 'google-linked@example.com',
        'access_token' => Crypt::encryptString('mock-token'),
    ]);

    $this->actingAs($regularUser);

    $response = $this->delete(route('workos.users.oauth.disconnect', [$user, $credential]));

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('auth_oauth_credentials', [
        'id' => $credential->id,
    ]);
});

test('admin can disconnect user oauth credential', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $user = User::factory()->create();

    $credential = AuthOAuthCredential::create([
        'user_id' => $user->id,
        'provider_id' => $this->provider->id,
        'external_id' => 'google-external-123',
        'email' => 'google-linked@example.com',
        'access_token' => Crypt::encryptString('mock-token'),
    ]);

    $this->actingAs($admin);

    $response = $this->delete(route('workos.users.oauth.disconnect', [$user, $credential]));

    $response->assertRedirect();
    $this->assertDatabaseMissing('auth_oauth_credentials', [
        'id' => $credential->id,
    ]);
});

test('cannot disconnect oauth credential of another user', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $credential = AuthOAuthCredential::create([
        'user_id' => $otherUser->id, // belongs to otherUser
        'provider_id' => $this->provider->id,
        'external_id' => 'google-external-123',
        'email' => 'google-linked@example.com',
        'access_token' => Crypt::encryptString('mock-token'),
    ]);

    $this->actingAs($admin);

    // Try to disconnect credentials belonging to otherUser by passing $user in route parameter
    $response = $this->delete(route('workos.users.oauth.disconnect', [$user, $credential]));

    $response->assertStatus(403);
    $this->assertDatabaseHas('auth_oauth_credentials', [
        'id' => $credential->id,
    ]);
});

test('admin can retrieve user sessions list', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $user = User::factory()->create();

    $session = AuthSession::create([
        'user_id' => $user->id,
        'session_token' => 'mock-token-1',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0 Chrome/120.0.0.0',
        'is_revoked' => false,
        'expires_at' => now()->addDays(2),
        'last_activity_at' => now(),
    ]);

    $this->actingAs($admin);

    $response = $this->get("/workos/users/{$user->id}/sessions");

    $response->assertOk();
    $response->assertJsonPath('sessions.0.id', $session->id);
});

test('admin can revoke user session', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $user = User::factory()->create();

    $session = AuthSession::create([
        'user_id' => $user->id,
        'session_token' => 'mock-token-1',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0 Chrome/120.0.0.0',
        'is_revoked' => false,
        'expires_at' => now()->addDays(2),
        'last_activity_at' => now(),
    ]);

    $this->actingAs($admin);

    $response = $this->delete("/workos/users/{$user->id}/sessions/{$session->id}");

    $response->assertOk();
    $this->assertTrue($session->fresh()->is_revoked);
});

test('admin can revoke all active sessions for a user', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $user = User::factory()->create();

    $session1 = AuthSession::create([
        'user_id' => $user->id,
        'session_token' => 'mock-token-1',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0 Chrome/120.0.0.0',
        'is_revoked' => false,
        'expires_at' => now()->addDays(2),
        'last_activity_at' => now(),
    ]);

    $session2 = AuthSession::create([
        'user_id' => $user->id,
        'session_token' => 'mock-token-2',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0 Firefox/120.0.0.0',
        'is_revoked' => false,
        'expires_at' => now()->addDays(2),
        'last_activity_at' => now(),
    ]);

    $this->actingAs($admin);

    $response = $this->delete("/workos/users/{$user->id}/sessions");

    $response->assertOk();
    $this->assertTrue($session1->fresh()->is_revoked);
    $this->assertTrue($session2->fresh()->is_revoked);
});

test('admin can clear inactive sessions for a user', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $user = User::factory()->create();

    $activeSession = AuthSession::create([
        'user_id' => $user->id,
        'session_token' => 'active-token',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0 Chrome/120.0.0.0',
        'is_revoked' => false,
        'expires_at' => now()->addDays(2),
        'last_activity_at' => now(),
    ]);

    $revokedSession = AuthSession::create([
        'user_id' => $user->id,
        'session_token' => 'revoked-token',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0 Firefox/120.0.0.0',
        'is_revoked' => true,
        'expires_at' => now()->addDays(2),
        'last_activity_at' => now(),
    ]);

    $expiredSession = AuthSession::create([
        'user_id' => $user->id,
        'session_token' => 'expired-token',
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Mozilla/5.0 Firefox/120.0.0.0',
        'is_revoked' => false,
        'expires_at' => now()->subDays(2),
        'last_activity_at' => now(),
    ]);

    $this->actingAs($admin);

    $response = $this->delete("/workos/users/{$user->id}/sessions/clear");

    $response->assertOk();
    $this->assertNotNull($activeSession->fresh());
    $this->assertNull($revokedSession->fresh());
    $this->assertNull($expiredSession->fresh());
});

test('admin can download user template', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $this->actingAs($admin);

    $response = $this->get('/workos/users/template?type=mahasiswa&format=csv');

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('Content-Disposition'))->toContain('attachment; filename=template_mahasiswa_');
});

test('admin can upload users via csv', function () {
    $fakultasId = DB::table('fakultas')->insertGetId([
        'nama' => 'FMIKOM',
        'kode' => 'FMIKOM',
    ]);

    $prodi = ProgramStudi::firstOrCreate(['kode' => 'IF'], [
        'nama' => 'Informatika',
        'fakultas_id' => $fakultasId,
    ]);

    Module::firstOrCreate(['code' => 'FAST'], ['name' => 'FAST', 'description' => 'FAST', 'is_active' => true]);
    Module::firstOrCreate(['code' => 'PAGI'], ['name' => 'PAGI', 'description' => 'PAGI', 'is_active' => true]);
    Module::firstOrCreate(['code' => 'WIMS'], ['name' => 'WIMS', 'description' => 'WIMS', 'is_active' => true]);
    Role::firstOrCreate(['slug' => 'mahasiswa'], ['nama' => 'Mahasiswa']);

    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $this->actingAs($admin);

    $csvContent = "Nama,Email,Password,NIM,Program Studi\nEko,eko@example.com,pass123,1234567,IF\n";
    $file = UploadedFile::fake()->createWithContent('mahasiswa.csv', $csvContent);

    $response = $this->post('/workos/users/upload', [
        'file' => $file,
        'user_type' => 'mahasiswa',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('users', [
        'email' => 'eko@example.com',
        'name' => 'Eko',
        'nomor_induk' => '1234567',
        'user_type' => 'mahasiswa',
        'is_active' => false,
        'email_verified_at' => null,
    ]);
});
