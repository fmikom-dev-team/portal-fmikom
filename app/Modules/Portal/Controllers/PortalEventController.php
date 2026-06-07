<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PortalEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;

class PortalEventController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $events = PortalEvent::when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('location', 'like', "%{$search}%");
            })
            ->latest()
            ->get();

        return Inertia::render('Modules/Portal/Admin/Events/Index', [
            'events' => $events,
            'filters' => $request->only(['search'])
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:portal_events,slug',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'registration_link' => 'nullable|url',
            'status' => 'required|in:draft,published',
            'thumbnail' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $this->processAndStoreImage($request->file('thumbnail'), 'portal/events/thumbnails');
        }

        PortalEvent::create($validated);

        return redirect()->back()->with('success', 'Event berhasil ditambahkan!');
    }

    public function update(Request $request, PortalEvent $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:portal_events,slug,' . $event->id,
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'registration_link' => 'nullable|url',
            'status' => 'required|in:draft,published',
            'thumbnail' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($event->thumbnail) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $event->thumbnail));
            }
            $validated['thumbnail'] = $this->processAndStoreImage($request->file('thumbnail'), 'portal/events/thumbnails');
        } else {
            unset($validated['thumbnail']);
        }

        $event->update($validated);

        return redirect()->back()->with('success', 'Event berhasil diperbarui!');
    }

    public function destroy(PortalEvent $event)
    {
        if ($event->thumbnail) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $event->thumbnail));
        }

        $event->delete();

        return redirect()->back()->with('success', 'Event berhasil dihapus!');
    }

    private function processAndStoreImage($file, $path)
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file);

        if ($image->width() > 1200) {
            $image->scale(width: 1200);
        }

        $filename = Str::random(40) . '.webp';
        $fullPath = $path . '/' . $filename;

        $encoded = $image->toWebp(80);
        Storage::disk('public')->put($fullPath, (string) $encoded);

        return '/storage/' . $fullPath;
    }
}
