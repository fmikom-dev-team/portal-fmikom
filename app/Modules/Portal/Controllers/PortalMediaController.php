<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Portal\PortalMedia;
use App\Services\VirusScannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PortalMediaController extends Controller
{
    public function index(Request $request)
    {
        $media = PortalMedia::latest()->get();

        if ($request->expectsJson()) {
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
            'files.*' => 'required|file|max:10240', // 10MB limit
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

                PortalMedia::create([
                    'filename' => $filename,
                    'path' => '/storage/'.$path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Media uploaded successfully!');
    }

    public function destroy(PortalMedia $media)
    {
        $filePath = str_replace('/storage/', '', $media->path);
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }
        $media->delete();

        return redirect()->back()->with('success', 'Media deleted successfully!');
    }
}
