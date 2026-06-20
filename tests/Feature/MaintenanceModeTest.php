<?php

use App\Models\Portal\PortalSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::forget('portal_settings');
});

test('public page loads normally when maintenance mode is disabled', function () {
    PortalSetting::updateOrCreate(['key' => 'maintenance_mode'], ['value' => '0']);

    $response = $this->get('/');
    $response->assertOk();
});

test('public page returns 503 and renders maintenance page when maintenance mode is enabled', function () {
    PortalSetting::updateOrCreate(['key' => 'maintenance_mode'], ['value' => '1']);
    PortalSetting::updateOrCreate(['key' => 'maintenance_message'], ['value' => 'Situs Pemeliharaan']);

    $response = $this->get('/');
    $response->assertStatus(503);
    $response->assertSee('Situs Pemeliharaan');
});

test('super admin can bypass maintenance mode', function () {
    PortalSetting::updateOrCreate(['key' => 'maintenance_mode'], ['value' => '1']);

    $superAdmin = User::factory()->create(['user_type' => 'super-admin']);

    $response = $this->actingAs($superAdmin)->get('/privacy-policy');
    $response->assertOk();
});

test('excluded routes are accessible during maintenance mode', function () {
    PortalSetting::updateOrCreate(['key' => 'maintenance_mode'], ['value' => '1']);

    $this->get('/login')->assertStatus(200);

    // Bypasses the 503 maintenance screen (might redirect, but should NOT be 503)
    $this->get('/two-factor-challenge')->assertStatus(302);
    $this->get('/auth/oauth/google/callback')->assertStatus(302);
});

test('super admin can update system settings via post request', function () {
    $superAdmin = User::factory()->create(['user_type' => 'super_admin']);

    $response = $this->actingAs($superAdmin)->post('/workos/settings/update', [
        'maintenance_mode' => '1',
        'maintenance_message' => 'Situs Sedang Diperbaiki',
    ]);

    $response->assertRedirect();

    expect(PortalSetting::where('key', 'maintenance_mode')->value('value'))->toBe('1');
    expect(PortalSetting::where('key', 'maintenance_message')->value('value'))->toBe('Situs Sedang Diperbaiki');
});

test('super admin can update system settings with full form', function () {
    $superAdmin = User::factory()->create(['user_type' => 'super_admin']);

    $response = $this->actingAs($superAdmin)->post('/workos/settings/update', [
        'brand_name' => 'Portal FMIKOM',
        'brand_description' => 'Sistem informasi terpadu.',
        'primary_color' => '#2563eb',
        'maintenance_mode' => '1',
        'maintenance_message' => 'Situs Sedang Diperbaiki',
        'public_registration' => '0',
    ]);

    $response->assertRedirect();

    expect(PortalSetting::where('key', 'maintenance_mode')->value('value'))->toBe('1');
    expect(PortalSetting::where('key', 'public_registration')->value('value'))->toBe('0');
});
