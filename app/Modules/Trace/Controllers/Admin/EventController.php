<?php

namespace App\Modules\Trace\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trace\StoreEventRequest;
use App\Http\Requests\Trace\UpdateEventRequest;
use App\Models\Tracer\Event;
use App\Models\Tracer\EventRegistration;
use App\Models\User;
use App\Notifications\Trace\NewEventCreated;
use Illuminate\Support\Facades\Notification;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Models\Tracer\ActivityLog;
use App\Modules\Trace\Services\ImageService;

class EventController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = Event::withCount('registrations');

        // Search by title
        if ($request->filled('search')) {
            $escaped = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $request->search);
            $query->where('title', 'like', '%' . $escaped . '%');
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('event_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('event_date', '<=', $request->date_to);
        }

        $events = $query->latest()->paginate(10)->withQueryString();

        return Inertia::render('Modules/Trace/Admin/Events/Index', [
            'events' => $events,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Modules/Trace/Admin/Events/Create');
    }

    public function store(StoreEventRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Normalize time to H:i format
        if (!empty($validated['event_time_start'])) {
            $validated['event_time_start'] = substr($validated['event_time_start'], 0, 5);
        }
        if (!empty($validated['event_time_end'])) {
            $validated['event_time_end'] = substr($validated['event_time_end'], 0, 5);
        }

        if ($request->hasFile('poster')) {
            $validated['poster_path'] = ImageService::compressToWebp(
                $request->file('poster'), 'events', quality: 80, maxWidth: 1200
            );
        }
        unset($validated['poster']);

        $validated['created_by'] = auth()->id();

        $event = Event::create($validated);
        ActivityLog::record('event.created', "Membuat event: {$event->title}", $event);

        if ($event->status === 'published') {
            // Use cursor to avoid loading all alumni into memory
            User::whereHas('alumniProfile')
                ->select('id', 'name', 'email')
                ->chunkById(200, function ($alumni) use ($event) {
                    Notification::send($alumni, new NewEventCreated(
                        $event->title,
                        \Carbon\Carbon::parse($event->event_date)->format('d M Y'),
                        $event->id
                    ));
                });
        }

        return redirect()->route('module.trace.admin.events.index')
            ->with('success', 'Event berhasil dibuat.');
    }

    public function show($id): InertiaResponse
    {
        $event = Event::withCount('registrations')
            ->findOrFail($id);

        $registrations = $event->registrations()->with('user')->get();

        $attendanceCount = $registrations->whereNotNull('attended_at')->count();

        return Inertia::render('Modules/Trace/Admin/Events/Show', [
            'event' => $event,
            'registrations' => $registrations,
            'attendanceCount' => $attendanceCount,
        ]);
    }

    public function edit($id): InertiaResponse
    {
        $event = Event::findOrFail($id);

        return Inertia::render('Modules/Trace/Admin/Events/Edit', [
            'event' => $event,
        ]);
    }

    public function update(UpdateEventRequest $request, $id): RedirectResponse
    {
        $event = Event::findOrFail($id);

        $validated = $request->validated();

        // Normalize time to H:i format for consistent storage
        if (!empty($validated['event_time_start'])) {
            $validated['event_time_start'] = substr($validated['event_time_start'], 0, 5);
        }
        if (!empty($validated['event_time_end'])) {
            $validated['event_time_end'] = substr($validated['event_time_end'], 0, 5);
        }

        if ($request->hasFile('poster')) {
            $validated['poster_path'] = ImageService::replaceWithWebp(
                $request->file('poster'), $event->poster_path, 'events', quality: 80, maxWidth: 1200
            );
        }
        unset($validated['poster']);

        $event->update($validated);
        ActivityLog::record('event.updated', "Memperbarui event: {$event->title}", $event);

        return redirect()->route('module.trace.admin.events.show', $id)
            ->with('success', 'Event berhasil diperbarui.');
    }

    public function updateStatus(Request $request, $id): RedirectResponse
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:draft,published,closed',
        ]);

        // Prevent invalid status transitions
        $invalidTransitions = [
            'closed' => ['draft'],
        ];

        if (isset($invalidTransitions[$event->status]) && in_array($validated['status'], $invalidTransitions[$event->status])) {
            return back()->with('error', 'Tidak dapat mengubah status dari "' . $event->status . '" ke "' . $validated['status'] . '".');
        }

        $event->update($validated);
        ActivityLog::record('event.status_changed', "Mengubah status event: {$event->title} → {$request->status}", $event, ['status' => $request->status]);

        return back()->with('success', 'Status event berhasil diperbarui.');
    }

    public function markAttendance(Request $request, $eventId, $registrationId): RedirectResponse
    {
        $registration = EventRegistration::where('event_id', $eventId)->findOrFail($registrationId);

        // Determine new state before update to avoid stale model issues
        $wasAttended = !is_null($registration->attended_at);
        $newAttendedAt = $wasAttended ? null : now();

        $registration->update(['attended_at' => $newAttendedAt]);

        $action = $wasAttended ? 'Membatalkan kehadiran' : 'Mencatat kehadiran';
        ActivityLog::record('event.attendance', $action, $registration);

        $message = $wasAttended ? 'Kehadiran dibatalkan.' : 'Kehadiran dicatat.';
        return back()->with('success', $message);
    }

    public function destroy($id): RedirectResponse
    {
        $event = Event::findOrFail($id);

        $posterPath = $event->poster_path;

        ActivityLog::record('event.deleted', "Menghapus event: {$event->title}", $event);
        $event->delete();

        if ($posterPath) {
            Storage::disk('public')->delete($posterPath);
        }

        return redirect()->route('module.trace.admin.events.index')
            ->with('success', 'Event berhasil dihapus.');
    }
}
