<?php

namespace App\Modules\Trace\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Tracer\JobApplicant;
use App\Models\Tracer\JobListing;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardMitraController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $mitra = $user->mitraProfile;

        if (!$mitra) {
            return redirect()->route('module.trace.open-job.mitra-profile-setup');
        }

        $jobStats = JobListing::where('mitra_id', $mitra->id)
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as active")
            ->selectRaw("SUM(CASE WHEN status = 'pending_review' THEN 1 ELSE 0 END) as pending")
            ->first();

        $applicantStats = JobApplicant::whereHas('jobListing', fn($q) => $q->where('mitra_id', $mitra->id))
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = 'applied' THEN 1 ELSE 0 END) as pending")
            ->first();

        $stats = [
            'total_jobs' => (int) $jobStats->total,
            'active_jobs' => (int) $jobStats->active,
            'pending_jobs' => (int) $jobStats->pending,
            'total_applicants' => (int) $applicantStats->total,
            'pending_applicants' => (int) $applicantStats->pending,
        ];

        $recentApplicants = JobApplicant::whereHas('jobListing', fn($q) => $q->where('mitra_id', $mitra->id))
            ->with(['alumni.user', 'jobListing:id,title'])
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
