<?php

namespace App\Modules\Trace\Controllers\Alumni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\Tracer\ProfilAlumni;
use App\Models\Tracer\CareerHistory;
use App\Models\Pagi\PagiCv;
use App\Models\Tracer\JobApplicant;
use App\Models\Tracer\EventRegistration;
use App\Models\Tracer\Kuesioner;
use App\Models\Tracer\Response;
use Carbon\Carbon;

class TraceAlumniDashboardController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->attributes->get('resolved_role', session('active_role'));
        $user = auth()->user();
        $profile = $user->alumniProfile()->with(['user', 'careers.employment', 'careers.education'])->first();

        $stats = [
            'hasProfile' => (bool) $profile,
            'completeness' => $profile?->completeness_percentage ?? 0,
            'currentStatus' => 'mencari_kerja',
            'totalCareers' => 0,
            'yearsOfExperience' => 'Belum ada',
        ];

        if ($profile) {
            $careers = $profile->careers;
            $currentCareer = $careers->where('is_current', true)->first();

            $stats['currentStatus'] = $currentCareer?->status ?? 'mencari_kerja';
            $stats['totalCareers'] = $careers->whereIn('status', ['bekerja', 'wirausaha'])->count();
        }

        // Cek apakah alumni sudah pernah mengisi kuesioner
        $hasFilledKuesioner = DB::table('responses')
            ->where('user_id', $user->id)
            ->exists();

        // --- Profile Completeness Checklist ---
        $completenessItems = [
            ['label' => 'Foto Profil', 'done' => !empty($user->foto_path)],
            ['label' => 'Data Pribadi', 'done' => !empty($profile?->jenis_kelamin)],
            ['label' => 'Alamat', 'done' => !empty($profile?->alamat_rumah)],
            ['label' => 'No. Telepon', 'done' => !empty($user->no_telepon)],
            ['label' => 'Riwayat Karir', 'done' => $profile ? CareerHistory::where('profil_alumni_id', $profile->id)->exists() : false],
            ['label' => 'CV/Portfolio', 'done' => PagiCv::where('user_id', $user->id)->exists()],
        ];

        $completenessPercentage = count($completenessItems) > 0
            ? round(collect($completenessItems)->where('done', true)->count() / count($completenessItems) * 100)
            : 0;

        $profileCompleteness = [
            'items' => $completenessItems,
            'percentage' => $completenessPercentage,
        ];

        // --- Quick Stats ---
        $appliedJobsCount = $profile
            ? JobApplicant::where('alumni_id', $profile->id)->count()
            : 0;

        $upcomingEventsCount = EventRegistration::where('user_id', $user->id)
            ->whereHas('event', function ($q) {
                $q->where('event_date', '>=', Carbon::today());
            })
            ->count();

        // Kuesioners that are active but the user hasn't responded to yet
        $answeredKuesionerIds = Response::where('user_id', $user->id)->pluck('kuesioner_id');
        $pendingKuesionersCount = Kuesioner::whereIn('status', ['active', 'published'])
            ->whereNotIn('id', $answeredKuesionerIds)
            ->count();

        return Inertia::render('Modules/Trace/Alumni/AlumniDashboard', [
            'moduleName' => 'TRACE',
            'roleName' => $role,
            'stats' => $stats,
            'hasFilledKuesioner' => $hasFilledKuesioner,
            'profileCompleteness' => $profileCompleteness,
            'appliedJobsCount' => $appliedJobsCount,
            'upcomingEventsCount' => $upcomingEventsCount,
            'pendingKuesionersCount' => $pendingKuesionersCount,
        ]);
    }
}