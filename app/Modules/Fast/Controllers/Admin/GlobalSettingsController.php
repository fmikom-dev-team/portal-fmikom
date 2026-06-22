<?php

namespace App\Modules\Fast\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateGlobalSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Support\FastStorage;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GlobalSettingsController extends Controller
{
    public function previewLogo(): SymfonyResponse|StreamedResponse
    {
        $logoPath = (string) TemplateGlobalSetting::get('logo_path', '');

        if (trim($logoPath) !== '' && FastStorage::exists($logoPath)) {
            return FastStorage::response(
                $logoPath,
                'fast-kop-logo.png',
                [
                    'Content-Type' => 'image/png',
                    'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                ],
            );
        }

        return response()->file(public_path('images/kop-logo-temp.png'), [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

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

            if (str_starts_with($existingLogo, '/private/')) {
                $existingLogo = substr($existingLogo, strlen('/private/'));
            } elseif (str_starts_with($existingLogo, 'private/')) {
                $existingLogo = substr($existingLogo, strlen('private/'));
            } else {
                $existingLogo = '';
            }

            $extension = $file?->extension() ?: $file?->getClientOriginalExtension() ?: 'png';
            $filename = 'fast-kop-logo-' . now()->format('YmdHis') . '.' . $extension;
            $path = $file->storeAs('fast/template', $filename, 'local');

            if ($existingLogo !== '' && $existingLogo !== $path) {
                FastStorage::delete($existingLogo);
            }

            $settings['logo_path'] = $path;
        }

        foreach ($settings as $key => $value) {
            TemplateGlobalSetting::set($key, $value);
        }

        // Clear semua cache setelah simpan
        TemplateGlobalSetting::clearCache();

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
