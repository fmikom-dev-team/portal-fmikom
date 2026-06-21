<?php

namespace App\Modules\Fast\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateGlobalSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GlobalSettingsController extends Controller
{
    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'settings' => ['nullable', 'array'],
            'logo_file' => ['nullable', 'image', 'max:3072'],
        ]);

        $settings = $validated['settings'] ?? [];

        if ($request->hasFile('logo_file')) {
            $file = $request->file('logo_file');
            $existingLogo = (string) TemplateGlobalSetting::get('logo_path', '');

            if (str_starts_with($existingLogo, '/storage/')) {
                $existingLogo = substr($existingLogo, strlen('/storage/'));
            } elseif (str_starts_with($existingLogo, 'storage/')) {
                $existingLogo = substr($existingLogo, strlen('storage/'));
            } else {
                $existingLogo = '';
            }

            $extension = $file?->extension() ?: $file?->getClientOriginalExtension() ?: 'png';
            $filename = 'fast-kop-logo-' . now()->format('YmdHis') . '.' . $extension;
            $path = $file->storeAs('fast/template', $filename, 'public');

            if ($existingLogo !== '' && $existingLogo !== $path && Storage::disk('public')->exists($existingLogo)) {
                Storage::disk('public')->delete($existingLogo);
            }

            $settings['logo_path'] = '/storage/' . $path;
        }

        foreach ($settings as $key => $value) {
            TemplateGlobalSetting::set($key, $value);
        }

        // Clear semua cache setelah simpan
        TemplateGlobalSetting::clearCache();

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
