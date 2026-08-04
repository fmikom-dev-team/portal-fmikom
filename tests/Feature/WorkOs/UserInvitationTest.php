<?php

use App\Models\User;
use App\Models\UserInvitation;
use App\Notifications\UserInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('admin can send user invitation', function () {
    Notification::fake();

    $admin = User::factory()->create(['user_type' => 'super_admin']);

    $response = $this->actingAs($admin)->post('/workos/invitations/send', [
        'first_name' => 'Budi',
        'last_name' => 'Santoso',
        'email' => 'budi.santoso@example.com',
        'user_type' => 'mahasiswa',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('user_invitations', [
        'email' => 'budi.santoso@example.com',
        'first_name' => 'Budi',
        'user_type' => 'mahasiswa',
        'status' => 'pending',
    ]);

    Notification::assertSentTo(
        Notification::route('mail', 'budi.santoso@example.com'),
        UserInvitationNotification::class
    );
});

test('invitee can accept invitation and set password', function () {
    $invitation = UserInvitation::create([
        'email' => 'rudi@example.com',
        'first_name' => 'Rudi',
        'last_name' => 'Hermawan',
        'user_type' => 'dosen',
        'token' => 'test-invitation-token-123456',
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    $responseGet = $this->get('/user-invitations/accept?token=test-invitation-token-123456');
    $responseGet->assertOk();

    $responsePost = $this->post('/user-invitations/accept', [
        'token' => 'test-invitation-token-123456',
        'name' => 'Rudi Hermawan',
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'SecurePassword123!',
    ]);

    $responsePost->assertRedirect('/workos');
    $this->assertDatabaseHas('users', [
        'email' => 'rudi@example.com',
        'name' => 'Rudi Hermawan',
        'user_type' => 'dosen',
        'status_approval' => 'approved',
    ]);

    $this->assertDatabaseHas('user_invitations', [
        'id' => $invitation->id,
        'status' => 'accepted',
    ]);
});

test('expired invitation token is rejected', function () {
    UserInvitation::create([
        'email' => 'expired@example.com',
        'first_name' => 'Expired',
        'user_type' => 'alumni',
        'token' => 'expired-token-999',
        'status' => 'pending',
        'expires_at' => now()->subDays(1),
    ]);

    $responsePost = $this->post('/user-invitations/accept', [
        'token' => 'expired-token-999',
        'name' => 'Expired User',
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'SecurePassword123!',
    ]);

    $responsePost->assertSessionHasErrors(['token']);
    $this->assertDatabaseMissing('users', [
        'email' => 'expired@example.com',
    ]);
});
