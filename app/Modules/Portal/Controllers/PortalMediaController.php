<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PortalMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Str;

class PortalMediaController extends Controller
{
    public function index()
    {
        $media = PortalMedia::latest()->get();
        return Inertia::render('Modules/Portal/Admin/Media', [
            'media' => $media
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'files.*' => 'required|file|max:10240' // 10MB limit
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filename = $file->getClientOriginalName();
                $path = $file->store('portal/media', 'public');
                
                PortalMedia::create([
                    'filename' => $filename,
                    'path' => '/storage/' . $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize()
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
