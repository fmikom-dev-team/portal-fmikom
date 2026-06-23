<?php

namespace App\Modules\Trace\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Tracer\JobApplicant;
use App\Models\Tracer\JobListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DashboardMitraController extends Controller
{
    public function index(Request $request): InertiaResponse|RedirectResponse
    {
        $mitra = $request->user()->mitraProfile;

        if (! $mitra) {
            return redirect()->route('module.trace.open-job.mitra-profile-setup');
        }

        $mitraId = $mitra->id;

        // Cache per-mitra stats for 5 minutes
        $stats = Cache::remember("trace_mitra_dashboard_{$mitraId}", now()->addMinutes(5), function () use ($mitraId) {
            $jobStats = JobListing::where('mitra_id', $mitraId)
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as active")
                ->selectRaw("SUM(CASE WHEN status = 'pending_review' THEN 1 ELSE 0 END) as pending")
                ->first();

            $applicantStats = JobApplicant::whereHas('jobListing', fn ($q) => $q->where('mitra_id', $mitraId))
                ->selectRaw('COUNT(*) as total')
                ->selectRaw("SUM(CASE WHEN status = 'applied' THEN 1 ELSE 0 END) as pending")
                ->first();

            return [
                'total_jobs' => (int) $jobStats->total,
                'active_jobs' => (int) $jobStats->active,
                'pending_jobs' => (int) $jobStats->pending,
                'total_applicants' => (int) $applicantStats->total,
                'pending_applicants' => (int) $applicantStats->pending,
            ];
        });

        // Recent applicants are always fresh (not cached)
        $recentApplicants = JobApplicant::whereHas('jobListing', fn ($q) => $q->where('mitra_id', $mitraId))
            ->with(['alumni.user:id,name,email,foto_path', 'jobListing:id,title'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Modules/Trace/Mitra/Dashboard', [
            'mitra' => $mitra,
            'stats' => $stats,
            'recentApplicants' => $recentApplicants,
        ]);
    }
}
