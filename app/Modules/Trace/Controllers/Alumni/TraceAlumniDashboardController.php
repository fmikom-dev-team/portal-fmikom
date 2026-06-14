<?php

namespace App\Modules\Trace\Controllers\Alumni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Tracer\ProfilAlumni;
use App\Models\Tracer\CareerHistory;

class TraceAlumniDashboardController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->attributes->get('resolved_role', session('active_role'));
        $user = auth()->user();
        $profile = $user->alumniProfile()->with(['user', 'careers'])->first();

        $stats = [
            'hasProfile' => (bool) $profile,
            'completeness' => $profile?->completeness_percentage ?? 0,
            'currentStatus' => 'mencari_kerja',
            'totalCareers' => 0,
            'yearsOfExperience' => 'Belum ada',
        ];

        if ($profile) {
            $careers = $profile->careers()->with(['employment', 'education'])->get();
            $currentCareer = $careers->where('is_current', true)->first();

            $stats['currentStatus'] = $currentCareer?->status ?? 'mencari_kerja';
            $stats['totalCareers'] = $careers->whereIn('status', ['bekerja', 'wirausaha'])->count();
        }

        return Inertia::render('Modules/Trace/Alumni/AlumniDashboard', [
            'moduleName' => 'TRACE',
            'roleName' => $role,
            'stats' => $stats,
            'hasFilledKuesioner' => false, // ganti dengan logic kuesioner kamu nanti
        ]);
    }
}