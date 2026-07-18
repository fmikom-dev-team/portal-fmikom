<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Portal\PortalEvent;
use App\Services\VirusScannerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class PortalEventController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        if ($search) {
            try {
                $matchIds = PortalEvent::search($search)->keys();
                $events = PortalEvent::whereIn('id', $matchIds)->latest()->get();
            } catch (\Throwable $e) {
                Log::warning('Meilisearch unavailable (Event), falling back to SQL.', ['error' => $e->getMessage()]);
                $events = PortalEvent::where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                })->latest()->get();
            }
        } else {
            $events = PortalEvent::latest()->get();
        }

        return Inertia::render('Modules/Portal/Admin/Events/Index', [
            'events' => $events,
            'filters' => $request->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'organizer' => 'nullable|string|max:255',
            'organizer_logo' => 'nullable|image|max:2048',
            'slug' => 'required|string|unique:portal_events,slug',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'registration_link' => 'nullable|url',
            'status' => 'required|in:draft,published,scheduled',
            'published_at' => 'nullable|required_if:status,scheduled|date',
            'thumbnail' => 'nullable|image|max:5120',
            'is_paid' => 'required|boolean',
            'price' => 'nullable|required_if:is_paid,true,1|integer|min:0',
            'audience_type' => 'required|in:umum,khusus',
            'is_quota_limited' => 'required|boolean',
            'quota' => 'nullable|required_if:is_quota_limited,true,1|integer|min:1',
        ]);

        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $this->processAndStoreImage($request->file('thumbnail'), 'portal/events/thumbnails');
        }

        if ($request->hasFile('organizer_logo')) {
            $validated['organizer_logo'] = $this->processAndStoreImage($request->file('organizer_logo'), 'portal/events/logos');
        }

        PortalEvent::create($validated);

        return redirect()->back()->with('success', 'Event berhasil ditambahkan!');
    }

    public function update(Request $request, PortalEvent $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'organizer' => 'nullable|string|max:255',
            'organizer_logo' => 'nullable|image|max:2048',
            'slug' => 'required|string|unique:portal_events,slug,'.$event->id,
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'registration_link' => 'nullable|url',
            'status' => 'required|in:draft,published,scheduled',
            'published_at' => 'nullable|required_if:status,scheduled|date',
            'thumbnail' => 'nullable|image|max:5120',
            'is_paid' => 'required|boolean',
            'price' => 'nullable|required_if:is_paid,true,1|integer|min:0',
            'audience_type' => 'required|in:umum,khusus',
            'is_quota_limited' => 'required|boolean',
            'quota' => 'nullable|required_if:is_quota_limited,true,1|integer|min:1',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($event->thumbnail) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $event->thumbnail));
            }
            $validated['thumbnail'] = $this->processAndStoreImage($request->file('thumbnail'), 'portal/events/thumbnails');
        } else {
            unset($validated['thumbnail']);
        }

        if ($request->hasFile('organizer_logo')) {
            if ($event->organizer_logo) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $event->organizer_logo));
            }
            $validated['organizer_logo'] = $this->processAndStoreImage($request->file('organizer_logo'), 'portal/events/logos');
        } else {
            unset($validated['organizer_logo']);
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
        $scanner = app(VirusScannerService::class);
        $scanResult = $scanner->scan($file);
        if (! $scanResult['safe']) {
            throw ValidationException::withMessages([
                'thumbnail' => $scanResult['reason'],
            ]);
        }

        $manager = new ImageManager(new Driver);
        $image = $manager->read($file);

        if ($image->width() > 1200) {
            $image->scale(width: 1200);
        }

        $filename = Str::random(40).'.webp';
        $fullPath = $path.'/'.$filename;

        $encoded = $image->toWebp(80);
        Storage::disk('public')->put($fullPath, (string) $encoded);

        return '/storage/'.$fullPath;
    }
}
