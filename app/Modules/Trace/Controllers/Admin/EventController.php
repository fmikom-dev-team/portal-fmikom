<?php

namespace App\Modules\Trace\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tracer\Event;
use App\Models\Tracer\EventRegistration;
use App\Models\User;
use App\Notifications\Trace\NewEventCreated;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\Tracer\ActivityLog;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::withCount('registrations');

        // Search by title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
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

    public function create()
    {
        return Inertia::render('Modules/Trace/Admin/Events/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'event_date' => 'required|date',
            'event_time_start' => 'nullable|string',
            'event_time_end' => 'nullable|string',
            'registration_deadline' => 'nullable|date',
            'max_participants' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,published,closed',
            'poster' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('poster')) {
            $validated['poster_path'] = $request->file('poster')->store('events', 'public');
        }
        unset($validated['poster']);

        $validated['created_by'] = auth()->id();

        $event = Event::create($validated);
        ActivityLog::record('event.created', "Membuat event: {$event->title}", $event);

        if ($event->status === 'published') {
            $alumni = User::whereHas('alumniProfile')->get();
            foreach ($alumni as $user) {
                $user->notify(new NewEventCreated($event->title, \Carbon\Carbon::parse($event->event_date)->format('d M Y'), $event->id));
            }
        }

        return redirect()->route('events.index')
            ->with('success', 'Event berhasil dibuat.');
    }

    public function show($id)
    {
        $event = Event::withCount('registrations')
            ->findOrFail($id);

        $registrations = $event->registrations()->with('user')->get();

        $attendanceCount = $event->registrations()->whereNotNull('attended_at')->count();

        return Inertia::render('Modules/Trace/Admin/Events/Show', [
            'event' => $event,
            'registrations' => $registrations,
            'attendanceCount' => $attendanceCount,
        ]);
    }

    public function edit($id)
    {
        $event = Event::findOrFail($id);

        return Inertia::render('Modules/Trace/Admin/Events/Edit', [
            'event' => $event,
        ]);
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'nullable|string|max:255',
            'event_date' => 'required|date',
            'event_time_start' => 'nullable|string',
            'event_time_end' => 'nullable|string',
            'registration_deadline' => 'nullable|date',
            'max_participants' => 'nullable|integer|min:1',
            'status' => 'required|in:draft,published,closed',
            'poster' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('poster')) {
            if ($event->poster_path) {
                Storage::disk('public')->delete($event->poster_path);
            }
            $validated['poster_path'] = $request->file('poster')->store('events', 'public');
        }
        unset($validated['poster']);

        $event->update($validated);
        ActivityLog::record('event.updated', "Memperbarui event: {$event->title}", $event);

        return redirect()->route('events.show', $id)
            ->with('success', 'Event berhasil diperbarui.');
    }

    public function updateStatus(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:draft,published,closed',
        ]);

        $event->update($validated);
        ActivityLog::record('event.status_changed', "Mengubah status event: {$event->title} → {$request->status}", $event, ['status' => $request->status]);

        return back()->with('success', 'Status event berhasil diperbarui.');
    }

    public function markAttendance(Request $request, $eventId, $registrationId)
    {
        $registration = EventRegistration::where('event_id', $eventId)->findOrFail($registrationId);

        $registration->update([
            'attended_at' => $registration->attended_at ? null : now(),
        ]);

        ActivityLog::record(
            'event.attendance',
            $registration->attended_at ? 'Mencatat kehadiran' : 'Membatalkan kehadiran',
            $registration
        );

        return back()->with('success', $registration->attended_at ? 'Kehadiran dicatat.' : 'Kehadiran dibatalkan.');
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);

        ActivityLog::record('event.deleted', "Menghapus event: {$event->title}", $event);
        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Event berhasil dihapus.');
    }
}
