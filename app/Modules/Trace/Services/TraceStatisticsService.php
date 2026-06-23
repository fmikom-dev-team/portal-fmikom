<?php

namespace App\Modules\Trace\Services;

use App\Models\Tracer\CareerHistory;
use App\Models\Tracer\ProfilAlumni;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TraceStatisticsService
{
    /**
     * Get all statistics dashboard data (cached 30 min).
     */
    public function getDashboardData(): array
    {
        return Cache::remember('trace_statistics_dashboard', 1800, function () {
            $totalAlumni = ProfilAlumni::count();

            return [
                'totalAlumni' => $totalAlumni,
                'absorptionRate' => $this->getAbsorptionRate($totalAlumni),
                'verticalRate' => $this->getVerticalAlignmentRate(),
                ...$this->getCareerStatusCounts(),
                'prodiStats' => $this->getProdiStats(),
                'kuesionerStats' => $this->getKuesionerStats($totalAlumni),
                'avgWaitingTime' => 0,
                'waitingTimePerProdi' => [],
                'waitingDistribution' => [],
                ...$this->getWaitingTimeStats(),
            ];
        });
    }

    private function getAbsorptionRate(int $totalAlumni): float
    {
        $absorbed = CareerHistory::where('is_current', true)
            ->whereIn('status', ['bekerja', 'wirausaha', 'lanjut_studi'])
            ->distinct('profil_alumni_id')
            ->count('profil_alumni_id');

        return $totalAlumni > 0 ? round(($absorbed / $totalAlumni) * 100, 1) : 0;
    }

    private function getVerticalAlignmentRate(): float
    {
        $techSectors = ['Teknologi', 'IT', 'Informatika', 'Digital', 'Software', 'TI', 'Teknologi Informasi', 'Komputer'];

        $workingAlumni = CareerHistory::where('is_current', true)
            ->whereIn('status', ['bekerja', 'wirausaha'])
            ->count();

        $verticalAligned = CareerHistory::join('employment', 'career_history.id', '=', 'employment.career_history_id')
            ->where('career_history.is_current', true)
            ->whereIn('career_history.status', ['bekerja', 'wirausaha'])
            ->where(function ($q) use ($techSectors) {
                foreach ($techSectors as $s) {
                    $q->orWhere('employment.sektor_industri', 'like', "%$s%");
                }
            })
            ->count();

        return $workingAlumni > 0 ? round(($verticalAligned / $workingAlumni) * 100, 1) : 0;
    }

    private function getProdiStats()
    {
        return DB::table('profil_alumnis')
            ->join('users', 'profil_alumnis.user_id', '=', 'users.id')
            ->join('program_studis', 'users.program_studi_id', '=', 'program_studis.id')
            ->leftJoin('career_history', function ($join) {
                $join->on('profil_alumnis.id', '=', 'career_history.profil_alumni_id')
                    ->where('career_history.is_current', true)
                    ->whereIn('career_history.status', ['bekerja', 'wirausaha', 'lanjut_studi']);
            })
            ->select(
                'program_studis.nama as program_studi',
                DB::raw('count(distinct profil_alumnis.id) as total_alumni'),
                DB::raw('count(distinct career_history.profil_alumni_id) as absorbed')
            )
            ->groupBy('program_studis.nama')
            ->get()
            ->map(fn ($prodi) => [
                'prodi' => $prodi->program_studi,
                'total' => $prodi->total_alumni,
                'absorbed' => $prodi->absorbed,
                'rate' => $prodi->total_alumni > 0
                    ? round(($prodi->absorbed / $prodi->total_alumni) * 100, 1)
                    : 0,
            ]);
    }

    private function getKuesionerStats(int $totalAlumni)
    {
        return DB::table('kuesioner')
            ->leftJoin('responses', 'kuesioner.id', '=', 'responses.kuesioner_id')
            ->select(
                'kuesioner.id',
                'kuesioner.judul',
                'kuesioner.tahun',
                'kuesioner.status',
                DB::raw('count(responses.id) as total_responses')
            )
            ->groupBy('kuesioner.id', 'kuesioner.judul', 'kuesioner.tahun', 'kuesioner.status')
            ->orderByDesc('kuesioner.tahun')
            ->get()
            ->map(fn ($k) => [
                'id' => $k->id,
                'judul' => $k->judul,
                'tahun' => $k->tahun,
                'status' => $k->status,
                'responses' => $k->total_responses,
                'response_rate' => $totalAlumni > 0
                    ? round(($k->total_responses / $totalAlumni) * 100, 1)
                    : 0,
            ]);
    }

    private function getCareerStatusCounts(): array
    {
        $statusCounts = CareerHistory::where('is_current', true)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'workingCount' => $statusCounts['bekerja'] ?? 0,
            'wirausahaCount' => $statusCounts['wirausaha'] ?? 0,
            'studyCount' => $statusCounts['lanjut_studi'] ?? 0,
            'seekingCount' => $statusCounts['mencari_kerja'] ?? 0,
        ];
    }

    private function getWaitingTimeStats(): array
    {
        $waitingTimeData = DB::table('profil_alumnis')
            ->join('users', 'profil_alumnis.user_id', '=', 'users.id')
            ->join('program_studis', 'users.program_studi_id', '=', 'program_studis.id')
            ->join('career_history', function ($join) {
                $join->on('profil_alumnis.id', '=', 'career_history.profil_alumni_id')
                    ->whereIn('career_history.status', ['bekerja', 'wirausaha']);
            })
            ->whereNotNull('users.tahun_lulus')
            ->whereNotNull('career_history.tanggal_mulai')
            ->select(
                'profil_alumnis.id',
                'program_studis.nama as program_studi',
                'users.tahun_lulus',
                DB::raw('MIN(career_history.tanggal_mulai) as first_job_date')
            )
            ->groupBy('profil_alumnis.id', 'program_studis.nama', 'users.tahun_lulus')
            ->get()
            ->map(function ($row) {
                $graduationDate = Carbon::createFromDate($row->tahun_lulus, 7, 1);
                $firstJobDate = Carbon::parse($row->first_job_date);
                $months = max(0, $graduationDate->diffInMonths($firstJobDate, false));

                return (object) [
                    'program_studi' => $row->program_studi,
                    'months' => $months,
                ];
            });

        $avgWaitingTime = $waitingTimeData->count() > 0
            ? round($waitingTimeData->avg('months'), 1)
            : 0;

        $waitingTimePerProdi = $waitingTimeData->groupBy('program_studi')
            ->map(fn ($group, $prodi) => [
                'prodi' => $prodi,
                'avg_months' => round($group->avg('months'), 1),
                'count' => $group->count(),
                'min_months' => $group->min('months'),
                'max_months' => $group->max('months'),
            ])->values();

        $waitingDistribution = [
            ['label' => '< 3 bulan', 'count' => $waitingTimeData->where('months', '<', 3)->count()],
            ['label' => '3-5 bulan', 'count' => $waitingTimeData->whereBetween('months', [3, 5])->count()],
            ['label' => '6-12 bulan', 'count' => $waitingTimeData->whereBetween('months', [6, 12])->count()],
            ['label' => '> 12 bulan', 'count' => $waitingTimeData->where('months', '>', 12)->count()],
        ];

        return [
            'avgWaitingTime' => $avgWaitingTime,
            'waitingTimePerProdi' => $waitingTimePerProdi,
            'waitingDistribution' => $waitingDistribution,
        ];
    }
}
