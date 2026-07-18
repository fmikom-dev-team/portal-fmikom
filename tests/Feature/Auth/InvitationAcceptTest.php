<?php

use App\Models\Auth\AuthInvitation;
use App\Models\Module;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->module = Module::firstOrCreate(['code' => 'WIMS'], ['name' => 'WIMS', 'is_active' => true]);
});

test('show accept invitation page does not modify state (GET)', function () {
    $invitation = AuthInvitation::create([
        'module_id' => $this->module->id,
        'email' => 'invited@example.com',
        'token' => 'invitation-token-123',
        'organization_name' => 'FMIKOM Dev',
        'role' => 'admin',
        'status' => 'Pending',
        'expires_at' => now()->addDays(7),
        'invited_by' => 'Super Admin',
    ]);

    $response = $this->get(route('invitations.accept', ['token' => 'invitation-token-123']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Invitations/AcceptConfirm')
        ->where('token', 'invitation-token-123')
        ->where('email', 'invited@example.com')
        ->where('organization', 'FMIKOM Dev')
        ->where('role', 'admin')
    );

    // Assert database state is unchanged (still Pending)
    $invitation->refresh();
    expect($invitation->status)->toBe('Pending');
});

test('submitting invitation accept form modifies state (POST)', function () {
    $invitation = AuthInvitation::create([
        'module_id' => $this->module->id,
        'email' => 'invited2@example.com',
        'token' => 'invitation-token-456',
        'organization_name' => 'FMIKOM Prod',
        'role' => 'admin',
        'status' => 'Pending',
        'expires_at' => now()->addDays(7),
        'invited_by' => 'Super Admin',
    ]);

    $response = $this->post(route('invitations.accept.post', ['token' => 'invitation-token-456']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Invitations/AcceptSuccess')
        ->where('status', 'accepted')
        ->where('email', 'invited2@example.com')
        ->where('organization', 'FMIKOM Prod')
        ->where('role', 'admin')
    );

    // Assert database state is updated to Accepted
    $invitation->refresh();
    expect($invitation->status)->toBe('Accepted');
});

test('expired invitation accept page shows error', function () {
    AuthInvitation::create([
        'module_id' => $this->module->id,
        'email' => 'invited3@example.com',
        'token' => 'invitation-token-789',
        'organization_name' => 'FMIKOM Prod',
        'role' => 'admin',
        'status' => 'Pending',
        'expires_at' => now()->subDay(), // Expired!
        'invited_by' => 'Super Admin',
    ]);

    $response = $this->get(route('invitations.accept', ['token' => 'invitation-token-789']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Invitations/InvalidToken')
        ->where('status', 'expired')
    );
});
