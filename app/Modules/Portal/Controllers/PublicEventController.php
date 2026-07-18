<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Portal\PortalEvent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PublicEventController extends Controller
{
    public function index(Request $request)
    {
        $now = now();

        $query = PortalEvent::where(function ($q) use ($now) {
            $q->where('status', 'published')
                ->orWhere(function ($sq) use ($now) {
                    $sq->where('status', 'scheduled')
                        ->where('published_at', '<=', $now);
                });
        });

        $allEvents = $query->get();

        $activeEvents = $allEvents->filter(function ($event) use ($now) {
            $compareTime = $event->end_time ?: $event->start_time;
            return $compareTime >= $now;
        })->sortBy('start_time')->values();

        $pastEvents = $allEvents->filter(function ($event) use ($now) {
            $compareTime = $event->end_time ?: $event->start_time;
            return $compareTime < $now;
        })->sortByDesc('start_time')->values();

        return Inertia::render('Modules/Portal/Event/Index', [
            'activeEvents' => $activeEvents,
            'pastEvents' => $pastEvents,
        ]);
    }

    public function show(string $slug)
    {
        $event = PortalEvent::where('slug', $slug)
            ->where(function ($q) {
                $q->where('status', 'published')
                    ->orWhere(function ($sq) {
                        $sq->where('status', 'scheduled')
                            ->where('published_at', '<=', now());
                    });
            })
            ->firstOrFail();

        return Inertia::render('Modules/Portal/Event/Show', [
            'event' => $event,
        ]);
    }
}
