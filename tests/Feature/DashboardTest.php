<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('visiting the dashboard clears active module session context', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    // Seed session with active module keys
    session([
        'active_module' => 'PAGI',
        'active_role' => 'mahasiswa',
        'active_module_at' => now()->toIso8601String(),
    ]);

    $response = $this->get(route('dashboard'));
    $response->assertOk();

    // Verify session keys are forgotten
    expect(session('active_module'))->toBeNull();
    expect(session('active_role'))->toBeNull();
    expect(session('active_module_at'))->toBeNull();
});
