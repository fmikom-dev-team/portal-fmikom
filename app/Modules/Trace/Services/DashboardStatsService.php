<?php

namespace App\Modules\Trace\Services;

use App\Models\Tracer\ProfilAlumni;
use App\Models\Tracer\CareerHistory;
use App\Models\Tracer\ActivityLog;
use App\Models\Tracer\Event;
use App\Models\Tracer\JobListing;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardStatsService
{
    public function getAdminStats(): array
    {
        // Cache heavy aggregate queries for 10 minutes
        // Activity logs are NOT cached (need to be real-time)
        $cachedStats = Cache::remember('trace_admin_dashboard_stats', now()->addMinutes(10), function () {
            $now = Carbon::now();
            $startOfThisMonth = $now->copy()->startOfMonth();

            $totalAlumni = ProfilAlumni::count();
            $totalAlumniLastMonth = ProfilAlumni::where('created_at', '<', $startOfThisMonth)->count();
            $alumniTrend = $this->calculateTrend($totalAlumni, $totalAlumniLastMonth);

            $workingNow = CareerHistory::where('is_current', true)
                ->whereIn('status', ['bekerja', 'wirausaha'])
                ->distinct('profil_alumni_id')
                ->count();
            
            $currentER = $totalAlumni > 0 ? ($workingNow / $totalAlumni) * 100 : 0;
            
            $workingLastMonth = CareerHistory::where('created_at', '<', $startOfThisMonth)
                ->whereIn('status', ['bekerja', 'wirausaha'])
                ->distinct('profil_alumni_id')
                ->count();
            
            $lastMonthER = $totalAlumniLastMonth > 0 ? ($workingLastMonth / $totalAlumniLastMonth) * 100 : 0;
            $erDiff = round($currentER - $lastMonthER, 1);
            $erTrend = ($erDiff >= 0 ? "+" : "") . $erDiff . "%";

            $studyingAlumni = CareerHistory::where('is_current', true)
                ->where('status', 'lanjut_studi')
                ->distinct('profil_alumni_id')
                ->count();

            $totalKuesioners = DB::table('kuesioner')->whereIn('status', ['active', 'published'])->count();
            $totalResponses = DB::table('responses')->count();

            $prodiData = DB::table('profil_alumnis')
                ->join('users', 'profil_alumnis.user_id', '=', 'users.id')
                ->join('program_studis', 'users.program_studi_id', '=', 'program_studis.id')
                ->select('program_studis.nama as program_studi', DB::raw('count(*) as count'))
                ->groupBy('program_studis.nama')
                ->get();

            $statusCounts = CareerHistory::where('is_current', true)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status');

            $careerBreakdown = [
                ['label' => 'Bekerja',       'value' => $statusCounts['bekerja'] ?? 0,       'color' => '#3b82f6'],
                ['label' => 'Wirausaha',     'value' => $statusCounts['wirausaha'] ?? 0,     'color' => '#10b981'],
                ['label' => 'Lanjut Studi',  'value' => $statusCounts['lanjut_studi'] ?? 0,  'color' => '#8b5cf6'],
                ['label' => 'Mencari Kerja', 'value' => $statusCounts['mencari_kerja'] ?? 0, 'color' => '#f59e0b'],
            ];

            $topSektors = DB::table('career_history')
                ->join('employment', 'career_history.id', '=', 'employment.career_history_id')
                ->where('career_history.is_current', true)
                ->whereIn('career_history.status', ['bekerja', 'wirausaha'])
                ->whereNotNull('employment.sektor_industri')
                ->where('employment.sektor_industri', '!=', '')
                ->select('employment.sektor_industri', DB::raw('count(*) as total'))
                ->groupBy('employment.sektor_industri')
                ->orderByDesc('total')
                ->limit(5)
                ->get()
                ->map(fn ($s) => ['name' => $s->sektor_industri, 'count' => $s->total])
                ->toArray();

            $topCities = DB::table('career_history')
                ->join('kota', 'career_history.kota_id', '=', 'kota.id')
                ->where('career_history.is_current', true)
                ->whereIn('career_history.status', ['bekerja', 'wirausaha'])
                ->select('kota.name as kota', DB::raw('count(*) as total'))
                ->groupBy('kota.name')
                ->orderByDesc('total')
                ->limit(5)
                ->get()
                ->map(fn ($c) => ['name' => $c->kota, 'count' => $c->total])
                ->toArray();

            $hasCareer = CareerHistory::distinct('profil_alumni_id')->count('profil_alumni_id');
            $hasCoordinates = ProfilAlumni::whereNotNull('latitude_rumah')
                ->whereNotNull('longitude_rumah')
                ->count();
            $hasFilledKuesioner = DB::table('responses')
                ->distinct('user_id')
                ->count('user_id');

            $dataCompleteness = [
                ['label' => 'Profil Terdaftar',   'value' => $totalAlumni,      'total' => $totalAlumni, 'rate' => 100],
                ['label' => 'Isi Data Karir',      'value' => $hasCareer,        'total' => $totalAlumni, 'rate' => $totalAlumni > 0 ? round(($hasCareer / $totalAlumni) * 100, 1) : 0],
                ['label' => 'Isi Koordinat Peta',  'value' => $hasCoordinates,   'total' => $totalAlumni, 'rate' => $totalAlumni > 0 ? round(($hasCoordinates / $totalAlumni) * 100, 1) : 0],
                ['label' => 'Isi Kuesioner',       'value' => $hasFilledKuesioner, 'total' => $totalAlumni, 'rate' => $totalAlumni > 0 ? round(($hasFilledKuesioner / $totalAlumni) * 100, 1) : 0],
            ];

            return [
                'totalAlumni' => [
                    'label' => 'Total Alumni',
                    'value' => number_format($totalAlumni),
                    'trend' => $alumniTrend . '%', 
                    'color' => 'blue',
                    'subValue' => $now->translatedFormat('F Y'),
                    'subLabel' => 'Update Terakhir'
                ],
                'employmentRate' => [
                    'label' => 'Employment Rate',
                    'value' => round($currentER, 1) . '%',
                    'trend' => $erTrend, 
                    'color' => 'green',
                ],
                'studiLanjut' => [
                    'label' => 'Lanjut Studi',
                    'value' => number_format($studyingAlumni),
                    'trend' => null, 
                    'color' => 'purple',
                    'subValue' => round(($totalAlumni > 0 ? ($studyingAlumni / $totalAlumni) * 100 : 0), 1) . '%',
                    'subLabel' => 'Dari Total Alumni'
                ],
                'kuesionerStats' => [
                    'total_kuesioners' => $totalKuesioners,
                    'total_responses' => $totalResponses,
                    'response_rate' => $totalAlumni > 0 ? round(($totalResponses / ($totalAlumni * max(1, $totalKuesioners))) * 100, 1) : 0,
                ],
                'careerBreakdown' => $careerBreakdown,
                'topSektors' => $topSektors,
                'topCities' => $topCities,
                'dataCompleteness' => $dataCompleteness,
                'prodiDistribution' => [
                    'labels' => $prodiData->pluck('program_studi')->toArray(),
                    'counts' => $prodiData->pluck('count')->toArray()
                ],
                'total_alumni_raw' => $totalAlumni
            ];
        });

        // Recent activities are NOT cached — always fresh
        $recentActivities = ActivityLog::with('user:id,name')
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'user_name' => $log->user?->name ?? 'System',
                'ip_address' => $log->ip_address,
                'created_at' => $log->created_at->toISOString(),
            ]);

        $cachedStats['recentActivities'] = $recentActivities;

        // Active jobs & events — not cached (real-time)
        $cachedStats['activeJobs'] = JobListing::where('status', 'approved')
            ->where(function ($q) {
                $q->whereNull('deadline')->orWhere('deadline', '>=', now());
            })->count();

        $cachedStats['activeEvents'] = Event::where('status', 'published')
            ->where('event_date', '>=', now()->toDateString())->count();

        $cachedStats['upcomingEvents'] = Event::where('status', 'published')
            ->where('event_date', '>=', now()->toDateString())
            ->orderBy('event_date', 'asc')
            ->limit(5)
            ->get(['id', 'title', 'event_date', 'event_time_start', 'location', 'max_participants'])
            ->map(fn ($e) => [
                'id' => $e->id,
                'title' => $e->title,
                'event_date' => $e->event_date,
                'event_time_start' => $e->event_time_start,
                'location' => $e->location,
                'max_participants' => $e->max_participants,
                'registrations_count' => $e->registrations()->count(),
            ]);

        $cachedStats['pendingJobs'] = JobListing::where('status', 'pending')->count();

        return $cachedStats;
    }

    public function getAlumniGrowthData(): array
    {
        return Cache::remember('trace_alumni_growth_data', now()->addMinutes(30), function () {
            // Data per angkatan per prodi
            $raw = DB::table('profil_alumnis')
                ->join('users', 'profil_alumnis.user_id', '=', 'users.id')
                ->join('program_studis', 'users.program_studi_id', '=', 'program_studis.id')
                ->whereNotNull('profil_alumnis.angkatan')
                ->select(
                    'profil_alumnis.angkatan',
                    'program_studis.nama as prodi',
                    DB::raw('count(*) as total')
                )
                ->groupBy('profil_alumnis.angkatan', 'program_studis.nama')
                ->orderBy('profil_alumnis.angkatan', 'asc')
                ->get();

            $angkatanList = $raw->pluck('angkatan')->unique()->sort()->values()->toArray();
            $prodiList = $raw->pluck('prodi')->unique()->sort()->values()->toArray();

            $perProdi = [];
            foreach ($prodiList as $prodi) {
                $perProdi[$prodi] = [];
                foreach ($angkatanList as $angkatan) {
                    $found = $raw->where('angkatan', $angkatan)->where('prodi', $prodi)->first();
                    $perProdi[$prodi][] = $found ? $found->total : 0;
                }
            }

            $totals = [];
            foreach ($angkatanList as $angkatan) {
                $totals[] = $raw->where('angkatan', $angkatan)->sum('total');
            }

            return [
                'labels' => $angkatanList,
                'totals' => $totals,
                'perProdi' => $perProdi,
                'prodiList' => $prodiList,
                'angkatanList' => $angkatanList,
            ];
        });
    }

    private function calculateTrend($current, $previous)
    {
        if ($previous <= 0) return $current > 0 ? '+100' : '+0';
        $diff = (($current - $previous) / $previous) * 100;
        $formatted = round($diff, 1);
        return ($formatted >= 0 ? "+" : "") . $formatted;
    }
}
