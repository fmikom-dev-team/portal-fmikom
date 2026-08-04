<?php

use App\Models\Portal\PortalSitelink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('super admin can fetch default google sitelinks', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);

    $response = $this->actingAs($admin)->get('/workos/settings/sitelinks');

    $response->assertOk();
    $response->assertJsonStructure([
        'sitelinks' => [
            '*' => ['id', 'title', 'description', 'url', 'icon', 'is_active'],
        ],
    ]);
});

test('super admin can create a new google sitelink', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);

    $response = $this->actingAs($admin)->post('/workos/settings/sitelinks', [
        'title' => 'Portal Berita FMIKOM',
        'description' => 'Berita dan pengumuman kegiatan akademik FMIKOM UNUGHA',
        'url' => '/posts',
        'icon' => 'Newspaper',
        'is_active' => true,
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('portal_sitelinks', [
        'title' => 'Portal Berita FMIKOM',
        'url' => '/posts',
    ]);
});

test('super admin can update existing sitelink', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $sitelink = PortalSitelink::create([
        'title' => 'Alumni Portal',
        'description' => 'Old description',
        'url' => '/alumni',
        'icon' => 'Link',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->put("/workos/settings/sitelinks/{$sitelink->id}", [
        'title' => 'Tracer Study & Alumni',
        'description' => 'Updated description',
        'url' => '/tracer',
        'icon' => 'GraduationCap',
        'is_active' => true,
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('portal_sitelinks', [
        'id' => $sitelink->id,
        'title' => 'Tracer Study & Alumni',
        'url' => '/tracer',
    ]);
});

test('super admin can delete sitelink', function () {
    $admin = User::factory()->create(['user_type' => 'super_admin']);
    $sitelink = PortalSitelink::create([
        'title' => 'Link to Delete',
        'url' => '/delete-me',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin)->delete("/workos/settings/sitelinks/{$sitelink->id}");

    $response->assertOk();
    $this->assertDatabaseMissing('portal_sitelinks', [
        'id' => $sitelink->id,
    ]);
});
