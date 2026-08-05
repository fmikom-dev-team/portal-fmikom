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

        $targetFilename = $media->filename;
        $targetPath = $media->path;

        // 1. Fetch all records sharing the same filename or path to clean up duplicates
        $matchingMedia = PortalMedia::where('id', $media->id)
            ->orWhere(function ($query) use ($targetFilename, $targetPath) {
                if (! empty($targetFilename)) {
                    $query->where('filename', $targetFilename);
                }
                if (! empty($targetPath)) {
                    $query->orWhere('path', $targetPath);
                }
            })
            ->get();

        foreach ($matchingMedia as $item) {
            // Physically delete file asset from disk storage
            if ($item->path) {
                $parsedPath = parse_url($item->path, PHP_URL_PATH);
                $relativePath = ltrim(str_replace('/storage/', '', $parsedPath), '/');
                if (! empty($relativePath) && Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                }
                // Clear any post thumbnail references matching this media path
                PortalPost::where('thumbnail', $item->path)->update(['thumbnail' => null]);

                // Clear occurrences in post content JSON if any
                $postsContainingPath = PortalPost::where('content', 'like', '%'.$item->path.'%')->get();
                foreach ($postsContainingPath as $post) {
                    $updatedContent = str_replace($item->path, '', (string) $post->content);
                    $post->update(['content' => $updatedContent]);
                }
            }
        }

        // 2. Delete all matching duplicate records from database
        PortalMedia::whereIn('id', $matchingMedia->pluck('id'))->delete();

        Cache::forget('portal_settings');
        Cache::forget('portal_featured_posts');

        // 3. Always return Inertia redirect back for web/Inertia requests
        if ($request->wantsJson() && ! $request->header('X-Inertia')) {
            return response()->json(['success' => true, 'message' => 'Media deleted successfully!'])
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        }

        return redirect()->back()->with('success', 'Media deleted successfully!');
    }
}
