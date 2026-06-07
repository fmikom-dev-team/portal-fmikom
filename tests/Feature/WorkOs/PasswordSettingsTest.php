<?php

use App\Models\User;
use App\Models\Auth\AuthSetting;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

test('password validation enforces min_length setting', function () {
    AuthSetting::set('email_password.enabled', true);
    AuthSetting::set('email_password.min_length', 12);
    
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'pass123', // 7 chars, too short
            'password_confirmation' => 'pass123',
        ]);

    $response->assertSessionHasErrors('password');
});

test('password validation enforces character requirement setting', function () {
    AuthSetting::set('email_password.enabled', true);
    AuthSetting::set('email_password.min_length', 8);
    AuthSetting::set('email_password.require_uppercase', true);
    AuthSetting::set('email_password.require_number', true);

    $user = User::factory()->create();

    // Try a password without uppercase and numbers
    $response = $this
        ->actingAs($user)
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'lowercaseonly',
            'password_confirmation' => 'lowercaseonly',
        ]);

    $response->assertSessionHasErrors('password');

    // Try a valid one
    $responseValid = $this
        ->actingAs($user)
        ->put(route('user-password.update'), [
            'current_password' => 'password',
            'password' => 'ValidPassword1',
            'password_confirmation' => 'ValidPassword1',
        ]);

    $responseValid->assertSessionHasNoErrors();
});

test('password history prevents reusing recent passwords', function () {
    AuthSetting::set('password.history_count', 3);

    $user = User::factory()->create([
        'password' => Hash::make('oldpassword1'),
    ]);

    // Seed password history
    DB::table('auth_password_histories')->insert([
        ['user_id' => $user->id, 'password_hash' => Hash::make('oldpassword1'), 'created_at' => now(), 'updated_at' => now()],
        ['user_id' => $user->id, 'password_hash' => Hash::make('oldpassword2'), 'created_at' => now(), 'updated_at' => now()],
    ]);

    // Try to update password to one of the history passwords
    $response = $this
        ->actingAs($user)
        ->put(route('user-password.update'), [
            'current_password' => 'oldpassword1',
            'password' => 'oldpassword2',
            'password_confirmation' => 'oldpassword2',
        ]);

    $response->assertSessionHasErrors('password');
});
