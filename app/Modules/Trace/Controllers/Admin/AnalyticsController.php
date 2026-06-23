<?php

namespace App\Modules\Trace\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tracer\CareerHistory;
use App\Models\Tracer\ProfilAlumni;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    public function index()
    {
        $data = Cache::remember('trace_analytics_dashboard', 900, function () {
            $totalAlumni = ProfilAlumni::count();

            // --- Distribusi Status Karir Terkini ---
            $statusDistribution = CareerHistory::where('is_current', true)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $careerStatusLabels = [
                'bekerja' => 'Bekerja',
                'wirausaha' => 'Wirausaha',
                'lanjut_studi' => 'Lanjut Studi',
                'mencari_kerja' => 'Tidak Bekerja',
            ];

            $careerStatus = [];
            foreach ($careerStatusLabels as $key => $label) {
                $careerStatus[] = [
                    'label' => $label,
                    'value' => $statusDistribution[$key] ?? 0,
                ];
            }

            // --- Alumni per Angkatan ---
            $alumniPerAngkatan = ProfilAlumni::select('angkatan', DB::raw('count(*) as total'))
                ->whereNotNull('angkatan')
                ->groupBy('angkatan')
                ->orderBy('angkatan', 'asc')
                ->get();

            // --- Top 7 Sektor Industri ---
            $topSektors = DB::table('career_history')
                ->join('employment', 'career_history.id', '=', 'employment.career_history_id')
                ->whereNotNull('employment.sektor_industri')
                ->where('employment.sektor_industri', '!=', '')
                ->select('employment.sektor_industri as sektor_industri', DB::raw('count(*) as total'))
                ->groupBy('employment.sektor_industri')
                ->orderByDesc('total')
                ->limit(7)
                ->get();

            // --- Distribusi Program Studi ---
            $prodiDistribution = ProfilAlumni::join('users', 'profil_alumnis.user_id', '=', 'users.id')
                ->join('program_studis', 'users.program_studi_id', '=', 'program_studis.id')
                ->select('program_studis.nama as program_studi', DB::raw('count(*) as total'))
                ->groupBy('program_studis.nama')
                ->orderByDesc('total')
                ->get();

            // --- KPI summary ---
            $workingCount = ($statusDistribution['bekerja'] ?? 0) + ($statusDistribution['wirausaha'] ?? 0);
            $studyCount = $statusDistribution['lanjut_studi'] ?? 0;
            $employmentRate = $totalAlumni > 0 ? round(($workingCount / $totalAlumni) * 100, 1) : 0;
            $studyRate = $totalAlumni > 0 ? round(($studyCount / $totalAlumni) * 100, 1) : 0;

            // --- Response rate ---
            $totalKuesioners = DB::table('kuesioner')->whereIn('status', ['active', 'published'])->count();
            $totalResponses = DB::table('responses')->count();
            $responseRate = $totalAlumni > 0 && $totalKuesioners > 0
                ? round(($totalResponses / ($totalAlumni * $totalKuesioners)) * 100, 1)
                : 0;

            return [
                'totalAlumni' => $totalAlumni,
                'employmentRate' => $employmentRate,
                'studyRate' => $studyRate,
                'responseRate' => $responseRate,
                'careerStatus' => $careerStatus,
                'alumniPerAngkatan' => $alumniPerAngkatan,
                'topSektors' => $topSektors,
                'prodiDistribution' => $prodiDistribution,
            ];
        });

        return Inertia::render('Modules/Trace/Admin/Analytics', $data);
    }
}
