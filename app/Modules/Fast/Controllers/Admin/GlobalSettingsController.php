<?php

namespace App\Modules\Fast\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateGlobalSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GlobalSettingsController extends Controller
{
    public function save(Request $request): RedirectResponse
    {
        $settings = $request->input('settings', []);

        foreach ($settings as $key => $value) {
            TemplateGlobalSetting::set($key, $value);
        }

        // Clear semua cache setelah simpan
        TemplateGlobalSetting::clearCache();

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
