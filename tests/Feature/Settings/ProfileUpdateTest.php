<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

define('NEW_EMAIL', 'newemail@example.com');

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('profile.edit'));

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => 'Test User',
            'bio' => 'New bio content',
            'location' => 'Bandung, Indonesia',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('profile.edit'));

    $user->refresh();

    expect($user->name)->toBe('Test User');
    expect($user->bio)->toBe('New bio content');
    expect($user->location)->toBe('Bandung, Indonesia');
});

test('user email can be updated from security page with valid password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'email_verified_at' => now(),
    ]);

    $response = $this
        ->actingAs($user)
        ->put('/settings/email', [
            'email' => NEW_EMAIL,
            'current_password' => 'password123',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $user->refresh();

    expect($user->email)->toBe(NEW_EMAIL);
    expect($user->email_verified_at)->toBeNull();
});

test('user email cannot be updated with invalid password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'email' => 'oldemail@example.com',
    ]);

    $response = $this
        ->actingAs($user)
        ->put('/settings/email', [
            'email' => NEW_EMAIL,
            'current_password' => 'wrong-password',
        ]);

    $response->assertSessionHasErrors('current_password');

    $user->refresh();

    expect($user->email)->toBe('oldemail@example.com');
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete(route('profile.destroy'), [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('home'));

    $this->assertGuest();
    expect($user->fresh())->toBeNull();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from(route('profile.edit'))
        ->delete(route('profile.destroy'), [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrors('password')
        ->assertRedirect(route('profile.edit'));

    expect($user->fresh())->not->toBeNull();
});
