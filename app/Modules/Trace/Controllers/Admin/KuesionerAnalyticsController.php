<?php

namespace App\Modules\Trace\Controllers\Admin;

use App\Modules\Trace\Actions\ExportKuesionerAction;
use App\Http\Controllers\Controller;
use App\Models\Tracer\Kuesioner;
use App\Models\Tracer\Response;
use App\Modules\Trace\Services\KuesionerAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class KuesionerAnalyticsController extends Controller
{
    public function __construct(
        protected KuesionerAnalyticsService $analyticsService,
        protected ExportKuesionerAction $exportAction,
    ) {}

    public function getAnalytics(Request $request, $id)
    {
        $cacheKey = "kuesioner_analytics_{$id}";

        $data = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($id) {
            $kuesioner = Kuesioner::with(['sections.pertanyaans.opsiJawabans'])->findOrFail($id);

            return $this->analyticsService->buildAnalytics($kuesioner);
        });

        // Add live response count (always fresh, not cached)
        $data['total_responses'] = Response::where('kuesioner_id', $id)->count();
        $data['last_updated'] = now()->toISOString();

        return response()->json($data);
    }

    public function liveStats($id)
    {
        // Validate that the kuesioner exists (404 if not)
        Kuesioner::findOrFail($id);

        $stats = Cache::remember("kuesioner_live_stats_{$id}", now()->addMinutes(1), function () use ($id) {
            $totalResponses = Response::where('kuesioner_id', $id)->count();
            $todayResponses = Response::where('kuesioner_id', $id)
                ->whereDate('created_at', today())
                ->count();
            $lastResponseAt = Response::where('kuesioner_id', $id)
                ->latest()
                ->value('created_at');

            return [
                'total_responses' => $totalResponses,
                'today_responses' => $todayResponses,
                'last_response_at' => $lastResponseAt?->toISOString(),
            ];
        });

        return response()->json([
            ...$stats,
            'cache_status' => Cache::has("kuesioner_analytics_{$id}") ? 'cached' : 'fresh',
        ]);
    }

    public function getRespondents(Request $request, $id)
    {
        $tahunLulus = $request->query('tahun_lulus');
        $prodi      = $request->query('prodi');

        $query = Response::where('kuesioner_id', $id)
            ->with(['user.alumniProfile'])
            ->latest();

        if ($tahunLulus || $prodi) {
            $query->whereHas('user', function ($q) use ($tahunLulus, $prodi) {
                if ($tahunLulus) {
                    $q->where('tahun_lulus', $tahunLulus);
                }
                if ($prodi) {
                    $q->whereHas('programStudi', function ($pq) use ($prodi) {
                        $pq->where('nama', $prodi);
                    });
                }
            });
        }

        return response()->json($query->paginate(20));
    }

    public function export(Request $request, $id)
    {
        try {
            $kuesioner = Kuesioner::with('sections.pertanyaans.opsiJawabans')->findOrFail($id);

            $tahunLulus = $request->query('tahun_lulus');
            $prodi      = $request->query('prodi');

            return $this->exportAction->execute($kuesioner, $tahunLulus, $prodi);
        } catch (\Exception $e) {
            Log::error('Kuesioner export failed', [
                'kuesioner_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Gagal mengekspor data. Silakan coba lagi.');
        }
    }
}
