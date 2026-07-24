<?php

use App\Mail\MagicLinkEmail;
use App\Models\Auth\AuthMagicLink;
use App\Models\User;
use App\Services\Auth\MagicLinkService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    Mail::fake();
});

test('users can request a magic link', function () {
    $user = User::factory()->create([
        'status_approval' => 'activated',
        'is_active' => true,
    ]);

    $response = $this->post(route('auth.magic-link.send'), [
        'email' => $user->email,
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['message']);

    // Check that an invitation or magic link email was queued
    Mail::assertQueued(MagicLinkEmail::class);
});

test('invalid users do not trigger magic link email but return success message (prevent enumeration)', function () {
    $response = $this->post(route('auth.magic-link.send'), [
        'email' => 'nonexistent@example.com',
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['message']);

    Mail::assertNothingQueued();
});

test('users can verify and login with a valid magic link', function () {
    $user = User::factory()->create([
        'status_approval' => 'activated',
        'is_active' => true,
    ]);

    $service = app(MagicLinkService::class);
    $service->send($user->email);

    $link = AuthMagicLink::where('email', $user->email)->latest()->first();
    expect($link)->not->toBeNull();
    expect($link->is_used)->toBeFalse();

    // Since token is random and token_hash is sha256 in DB, we need the raw token.
    // The service doesn't return raw token from send(), so let's mock the token.
    $plainToken = 'test-token-123-456-789-000-111-222-333-444-555-666-777-888-999';
    $link->update(['token_hash' => hash('sha256', $plainToken)]);

    $url = URL::temporarySignedRoute(
        'auth.magic-link.verify',
        now()->addMinutes(15),
        [
            'token' => $plainToken,
            'email' => $user->email,
        ]
    );

    $response = $this->get($url);

    $response->assertRedirect(route('dashboard'));
    $this->assertAuthenticatedAs($user);

    $link->refresh();
    expect($link->is_used)->toBeTrue();
});
