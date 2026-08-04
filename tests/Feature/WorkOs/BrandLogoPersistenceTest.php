<?php

use App\Models\Portal\PortalSetting;
use Illuminate\Support\Facades\Cache;

test('brand_logo and brand_favicon from portal_settings database are preserved in Inertia shared state across cache clears', function () {
    // 1. Set custom logo and favicon in portal_settings database
    $customLogo = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
    $customFavicon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

    PortalSetting::updateOrCreate(['key' => 'brand_logo'], ['value' => $customLogo]);
    PortalSetting::updateOrCreate(['key' => 'brand_favicon'], ['value' => $customFavicon]);

    // 2. Clear cache as happens on Dokploy deployment
    Cache::forget('portal_settings');

    // 3. Request page and verify siteSettings contains the custom logo & favicon from DB
    $response = $this->get('/');

    $response->assertOk();
    $siteSettings = $response->viewData('page')['props']['siteSettings'] ?? [];

    expect($siteSettings['brand_logo'])->toBe($customLogo);
    expect($siteSettings['brand_favicon'])->toBe($customFavicon);
});

test('brand_logo stored as file path is preserved and not forcefully overridden on cache refresh', function () {
    $filePath = '/storage/branding/custom-logo.png';
    PortalSetting::updateOrCreate(['key' => 'brand_logo'], ['value' => $filePath]);

    Cache::forget('portal_settings');

    $response = $this->get('/');
    $response->assertOk();

    $siteSettings = $response->viewData('page')['props']['siteSettings'] ?? [];
    expect($siteSettings['brand_logo'])->toBe($filePath);
});
