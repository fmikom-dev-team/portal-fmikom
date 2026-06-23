<?php

namespace App\Modules\Trace\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\Pagi\PagiCv;
use App\Models\Tracer\Event;
use App\Models\Tracer\EventRegistration;
use App\Models\Tracer\JobApplicant;
use App\Models\Tracer\Kuesioner;
use App\Models\Tracer\Response;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class TraceAlumniDashboardController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $role = $request->attributes->get('resolved_role', session('active_role'));
        $user = auth()->user();
        $userId = $user->id;

        // Cache per-user dashboard data for 2 minutes
        $dashboardData = Cache::remember("trace_alumni_dashboard_{$userId}", now()->addMinutes(2), function () use ($user, $userId) {
            $profile = $user->alumniProfile()->with(['careers.employment', 'careers.education'])->first();

            $stats = [
                'hasProfile' => (bool) $profile,
                'currentStatus' => 'mencari_kerja',
                'totalCareers' => 0,
            ];

            $profileId = $profile?->id;

            if ($profile) {
                $careers = $profile->careers;
                $currentCareer = $careers->where('is_current', true)->first();

                $stats['currentStatus'] = $currentCareer?->status ?? 'mencari_kerja';
                // Count ALL career records, not just bekerja/wirausaha
                $stats['totalCareers'] = $careers->count();
            }

            $hasCareer = $profile ? $profile->careers->isNotEmpty() : false;
            $hasCv = PagiCv::where('user_id', $userId)->exists();

            $completenessItems = [
                ['label' => 'Foto Profil', 'done' => ! empty($user->foto_path)],
                ['label' => 'Data Pribadi', 'done' => ! empty($profile?->jenis_kelamin)],
                ['label' => 'Alamat', 'done' => ! empty($profile?->alamat_rumah)],
                ['label' => 'No. Telepon', 'done' => ! empty($user->no_telepon)],
                ['label' => 'Riwayat Karir', 'done' => $hasCareer],
                ['label' => 'CV/Portfolio', 'done' => $hasCv],
            ];

            $completenessPercentage = count($completenessItems) > 0
                ? round(collect($completenessItems)->where('done', true)->count() / count($completenessItems) * 100)
                : 0;

            $appliedJobsCount = $profileId
                ? JobApplicant::where('alumni_id', $profileId)->count()
                : 0;

            // Recent job applications (latest 3)
            $recentApplications = $profileId
                ? JobApplicant::where('alumni_id', $profileId)
                    ->with('jobListing:id,title,location_city,tipe_kerja,status')
                    ->latest()
                    ->limit(3)
                    ->get()
                    ->map(fn ($app) => [
                        'id' => $app->id,
                        'job_title' => $app->jobListing?->title ?? 'Lowongan Dihapus',
                        'location' => $app->jobListing?->location_city,
                        'tipe_kerja' => $app->jobListing?->tipe_kerja,
                        'status' => $app->status ?? 'pending',
                        'applied_at' => $app->created_at?->toISOString(),
                    ])
                : [];

            $upcomingEventsCount = EventRegistration::where('user_id', $userId)
                ->whereHas('event', fn ($q) => $q->where('event_date', '>=', Carbon::today()))
                ->count();

            // Upcoming events (user registered + public upcoming)
            $registeredEventIds = EventRegistration::where('user_id', $userId)->pluck('event_id');

            $upcomingEvents = Event::where('status', 'published')
                ->where('event_date', '>=', Carbon::today())
                ->orderBy('event_date', 'asc')
                ->limit(6)
                ->get(['id', 'title', 'event_date', 'event_time_start', 'location', 'poster_path'])
                ->map(fn ($e) => [
                    'id' => $e->id,
                    'title' => $e->title,
                    'event_date' => $e->event_date?->toDateString(),
                    'event_time_start' => $e->event_time_start,
                    'location' => $e->location,
                    'poster_path' => $e->poster_path,
                    'is_registered' => $registeredEventIds->contains($e->id),
                ]);

            $answeredKuesionerIds = Response::where('user_id', $userId)
                ->pluck('kuesioner_id');

            $pendingQuery = Kuesioner::whereIn('status', ['active', 'published'])
                ->whereNotIn('id', $answeredKuesionerIds);

            $pendingKuesionersCount = $pendingQuery->count();

            $pendingKuesioners = $pendingQuery->get(['id', 'judul', 'deskripsi'])
                ->map(fn ($k) => [
                    'id' => $k->id,
                    'title' => $k->judul,
                    'deskripsi' => $k->deskripsi,
                ])
                ->toArray();

            $totalKuesionersCount = Kuesioner::whereIn('status', ['active', 'published'])->count();

            // Use pending count to determine if there are pending kuesioners
            $hasFilledKuesioner = $pendingKuesionersCount === 0 && $answeredKuesionerIds->isNotEmpty();

            return [
                'stats' => $stats,
                'hasFilledKuesioner' => $hasFilledKuesioner,
                'profileCompleteness' => [
                    'items' => $completenessItems,
                    'percentage' => $completenessPercentage,
                ],
                'appliedJobsCount' => $appliedJobsCount,
                'recentApplications' => $recentApplications,
                'upcomingEventsCount' => $upcomingEventsCount,
                'upcomingEvents' => $upcomingEvents,
                'pendingKuesionersCount' => $pendingKuesionersCount,
                'pendingKuesioners' => $pendingKuesioners,
                'totalKuesionersCount' => $totalKuesionersCount,
                'angkatan' => $profile?->angkatan,
                'programStudi' => $user->programStudi?->nama,
            ];
        });

        return Inertia::render('Modules/Trace/Alumni/AlumniDashboard', [
            'moduleName' => 'TRACE',
            'roleName' => $role,
            ...$dashboardData,
        ]);
    }
}
