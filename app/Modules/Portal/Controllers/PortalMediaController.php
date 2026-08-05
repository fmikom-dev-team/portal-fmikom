<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Portal\PortalMedia;
use App\Models\Portal\PortalPost;
use App\Services\VirusScannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
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
        if (! $media->exists) {
            $routeParam = $request->route('media') ?? $request->route('medium');
            if ($routeParam) {
                $media = $routeParam instanceof PortalMedia
                    ? $routeParam
                    : PortalMedia::findOrFail($routeParam);
            }
        }

        $targetPath = $media->path;

        // 1. Reference counting check: only delete physical file if no other PortalMedia record uses this path
        if (! empty($targetPath)) {
            $otherMediaCount = PortalMedia::where('path', $targetPath)
                ->where('id', '!=', $media->id)
                ->count();

            if ($otherMediaCount === 0) {
                $parsedPath = parse_url($targetPath, PHP_URL_PATH);
                $relativePath = ltrim(str_replace('/storage/', '', (string) $parsedPath), '/');
                if (! empty($relativePath) && Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                }
            }
        }

        // 2. Delete ONLY the target single media record (decoupled from PortalPost content)
        $media->delete();

        Cache::forget('portal_settings');
        Cache::forget('portal_featured_posts');

        // 3. Return Inertia redirect or JSON response
        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return response()->json(['success' => true, 'message' => 'Media deleted successfully!'])
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        }

        return redirect()->back()->with('success', 'Media deleted successfully!');
    }
}
