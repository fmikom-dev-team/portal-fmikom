<?php

namespace App\Modules\Trace\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\Tracer\Event;
use App\Models\Tracer\EventRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Notifications\Trace\EventRegistrationConfirmed;
use App\Notifications\Trace\NewEventRegistration;
use App\Models\Tracer\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class EventController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = Event::whereIn('status', ['published', 'closed'])
            ->withCount('registrations');

        // Search
        if ($request->filled('search')) {
            $search = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Time filter
        if ($request->filled('waktu')) {
            $today = now()->toDateString();
            match ($request->waktu) {
                'upcoming' => $query->where('event_date', '>', $today)->where('status', 'published'),
                'ongoing' => $query->whereDate('event_date', $today)->where('status', 'published'),
                'past' => $query->where(function ($q) use ($today) {
                    $q->where('event_date', '<', $today)->orWhere('status', 'closed');
                }),
                default => null,
            };
        }

        $events = $query->latest('event_date')->paginate(12)->withQueryString();

        return Inertia::render('Modules/Trace/Alumni/Events/Index', [
            'events' => $events,
            'filters' => $request->only(['search', 'waktu']),
        ]);
    }

    public function show($id): InertiaResponse
    {
        $event = Event::whereIn('status', ['published', 'closed'])
            ->withCount('registrations')
            ->findOrFail($id);

        $isRegistered = EventRegistration::where('event_id', $event->id)
            ->where('user_id', auth()->id())
            ->where('status', 'registered')
            ->exists();

        return Inertia::render('Modules/Trace/Alumni/Events/Show', [
            'event' => $event,
            'isRegistered' => $isRegistered,
        ]);
    }

    public function register($id): RedirectResponse
    {
        $event = Event::where('status', 'published')->findOrFail($id);

        $result = DB::transaction(function () use ($event) {
            $alreadyRegistered = EventRegistration::where('event_id', $event->id)
                ->where('user_id', auth()->id())
                ->where('status', 'registered')
                ->lockForUpdate()
                ->exists();

            if ($alreadyRegistered) {
                return ['error' => 'Anda sudah terdaftar pada event ini.'];
            }

            if ($event->registration_deadline && now()->greaterThan($event->registration_deadline)) {
                return ['error' => 'Batas waktu pendaftaran sudah berakhir.'];
            }

            if ($event->event_date && now()->startOfDay()->greaterThan($event->event_date)) {
                return ['error' => 'Event ini sudah berlalu.'];
            }

            if ($event->max_participants) {
                $currentCount = EventRegistration::where('event_id', $event->id)
                    ->where('status', 'registered')
                    ->lockForUpdate()
                    ->count();

                if ($currentCount >= $event->max_participants) {
                    return ['error' => 'Kuota peserta sudah penuh.'];
                }
            }

            // Reactivate cancelled registration instead of creating duplicate
            $existingCancelled = EventRegistration::where('event_id', $event->id)
                ->where('user_id', auth()->id())
                ->where('status', 'cancelled')
                ->first();

            if ($existingCancelled) {
                $existingCancelled->update(['status' => 'registered', 'registered_at' => now()]);
            } else {
                EventRegistration::create([
                    'event_id' => $event->id,
                    'user_id' => auth()->id(),
                    'status' => 'registered',
                    'registered_at' => now(),
                ]);
            }

            return ['success' => true];
        });

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        // Notify alumni about successful registration
        auth()->user()->notify(new EventRegistrationConfirmed(
            $event->title,
            $event->event_date->format('d M Y'),
            $event->id,
        ));

        // Notify admins about new registration
        $currentCount = EventRegistration::where('event_id', $event->id)->where('status', 'registered')->count();
        $admins = User::where('user_type', 'admin')->get();
        Notification::send($admins, new NewEventRegistration(
            auth()->user()->name,
            $event->title,
            $event->id,
            $currentCount,
            $event->max_participants,
        ));

        ActivityLog::record('event.registered', "Mendaftar event: {$event->title}", $event);

        return redirect()->back()->with('success', 'Berhasil mendaftar pada event.');
    }

    public function cancelRegistration($id): RedirectResponse
    {
        $registration = EventRegistration::where('event_id', $id)
            ->where('user_id', auth()->id())
            ->where('status', 'registered')
            ->firstOrFail();

        $registration->update([
            'status' => 'cancelled',
        ]);

        $event = Event::find($registration->event_id);
        ActivityLog::record('event.unregistered', "Membatalkan pendaftaran event: " . ($event->title ?? ''), $event);

        return redirect()->back()->with('success', 'Pendaftaran berhasil dibatalkan.');
    }

    public function myEvents(): InertiaResponse
    {
        $registrations = EventRegistration::where('user_id', auth()->id())
            ->with('event')
            ->latest('registered_at')
            ->paginate(12);

        return Inertia::render('Modules/Trace/Alumni/Events/MyEvents', [
            'registrations' => $registrations,
        ]);
    }
}
