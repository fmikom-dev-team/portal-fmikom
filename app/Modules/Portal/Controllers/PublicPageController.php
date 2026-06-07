<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Portal\PortalAcademicCalendar;
use App\Models\Portal\PortalEvent;
use App\Models\Portal\PortalPage;
use Inertia\Inertia;

class PublicPageController extends Controller
{
    public function show($slug)
    {
        $page = PortalPage::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $academicCalendars = [];
        $events = [];

        if ($slug === 'kalender-akademik') {
            $academicCalendars = PortalAcademicCalendar::orderBy('start_date', 'asc')->get();
        } elseif ($slug === 'agenda-event') {
            $events = PortalEvent::where('status', 'published')->orderBy('start_time', 'asc')->get();
        }

        return Inertia::render('Public/Page', [
            'page' => $page,
            'academicCalendars' => $academicCalendars,
            'events' => $events,
        ]);
    }
}
