<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Portal\PortalMedia;
use App\Services\VirusScannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PortalMediaController extends Controller
{
    public function index(Request $request)
    {
        $media = PortalMedia::latest()->get();

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'media' => $media,
            ]);
        }

        return Inertia::render('Modules/Portal/Admin/Media', [
            'media' => $media,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|mimes:jpg,jpeg,png,gif,webp,pdf,mp4,webm|max:10240', // 10MB limit
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $scanner = app(VirusScannerService::class);
                $scanResult = $scanner->scan($file);
                if (! $scanResult['safe']) {
                    throw ValidationException::withMessages([
                        'files' => $scanResult['reason'],
                    ]);
                }

                $filename = $file->getClientOriginalName();
                $path = $file->store('portal/media', 'public');
                $publicUrl = '/storage/'.$path;

                PortalMedia::firstOrCreate(
                    ['path' => $publicUrl],
                    [
                        'filename' => $filename,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Media uploaded successfully!');
    }

    public function destroy(Request $request, PortalMedia $media)
    {
        $media->delete();

        Cache::forget('portal_settings');

        if ($request->expectsJson() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true, 'message' => 'Media deleted successfully!']);
        }

        return redirect()->route('portal-admin.media.index')->with('success', 'Media deleted successfully!');
    }
}
