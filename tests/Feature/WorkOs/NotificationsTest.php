<?php

use App\Models\Radar\RadarDevice;
use App\Models\Radar\RadarProtection;
use App\Models\User;
use App\Notifications\WorkOsAlert;
use App\Modules\WorkOs\Services\Radar\DetectionEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::create([
        'name' => 'Super Admin',
        'email' => 'admin@fmikom.org',
        'password' => bcrypt('password123'),
        'user_type' => 'super-admin',
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
});

test('it seeds default notifications on first access', function () {
    $this->actingAs($this->admin);

    $response = $this->get('/workos/notifications');
    $response->assertStatus(200);

    expect($this->admin->notifications()->count())->toBe(4);
    expect($this->admin->unreadNotifications()->count())->toBe(2);
});

test('it can mark all notifications as read', function () {
    $this->actingAs($this->admin);

    $this->get('/workos/notifications');
    expect($this->admin->unreadNotifications()->count())->toBe(2);

    $response = $this->post('/workos/notifications/mark-all-read');
    $response->assertRedirect();

    expect($this->admin->unreadNotifications()->count())->toBe(0);
});

test('it can clear all notifications', function () {
    $this->actingAs($this->admin);

    $this->get('/workos/notifications');
    expect($this->admin->notifications()->count())->toBe(4);

    $response = $this->post('/workos/notifications/clear');
    $response->assertRedirect();

    expect($this->admin->notifications()->count())->toBe(0);
});

test('it can toggle individual notification read status', function () {
    $this->actingAs($this->admin);

    $this->get('/workos/notifications');
    $notification = $this->admin->unreadNotifications()->first();
    expect($notification)->not->toBeNull();

    // Toggle unread -> read
    $response = $this->post("/workos/notifications/{$notification->id}/toggle-read");
    $response->assertRedirect();

    $notification->refresh();
    expect($notification->read_at)->not->toBeNull();

    // Toggle read -> unread
    $response = $this->post("/workos/notifications/{$notification->id}/toggle-read");
    $response->assertRedirect();

    $notification->refresh();
    expect($notification->read_at)->toBeNull();
});

test('it records radar detection and sends notification to active super admins', function () {
    $protection = RadarProtection::create([
        'code' => 'bot_detection',
        'name' => 'Bot detection',
        'status' => 'Enabled',
        'sensitivity_level' => 50,
    ]);

    $device = RadarDevice::create([
        'device_fingerprint' => 'test_fingerprint',
        'ip_address' => '127.0.0.1',
        'is_trusted' => false,
        'last_seen_at' => now(),
    ]);

    $engine = new DetectionEngine();
    $engine->recordDetection(
        $protection,
        $device,
        'Bot detection',
        'Medium',
        65,
        'Logged',
        '127.0.0.1',
        ['user_agent' => 'curl/7.68.0']
    );

    $notifCount = $this->admin->notifications()
        ->where('type', WorkOsAlert::class)
        ->count();
    expect($notifCount)->toBe(1);

    $notification = $this->admin->notifications()->first();
    expect($notification->data['title'])->toContain('Bot detection');
});
