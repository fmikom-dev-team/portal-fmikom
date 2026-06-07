<?php

namespace App\Modules\Portal\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Portal\PortalAcademicCalendar;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PortalAcademicCalendarController extends Controller
{
    public function index()
    {
        $events = PortalAcademicCalendar::orderBy('start_date', 'asc')->get();
        return Inertia::render('Modules/Portal/Admin/AcademicCalendar/Index', [
            'events' => $events
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category' => 'required|string|in:akademik,kegiatan,libur,ujian,registrasi',
            'color' => 'required|string|in:blue,green,red,amber,purple',
        ]);

        PortalAcademicCalendar::create($validated);

        return redirect()->back()->with('success', 'Jadwal akademik berhasil ditambahkan!');
    }

    public function update(Request $request, PortalAcademicCalendar $academicCalendar)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'category' => 'required|string|in:akademik,kegiatan,libur,ujian,registrasi',
            'color' => 'required|string|in:blue,green,red,amber,purple',
        ]);

        $academicCalendar->update($validated);

        return redirect()->back()->with('success', 'Jadwal akademik berhasil diperbarui!');
    }

    public function destroy(PortalAcademicCalendar $academicCalendar)
    {
        $academicCalendar->delete();
        return redirect()->back()->with('success', 'Jadwal akademik berhasil dihapus!');
    }
}
