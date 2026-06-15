<?php

namespace App\Modules\Trace\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Tracer\JobApplycants;
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

        $stats = [
            'total_jobs' => JobListing::where('mitra_id', $mitra->id)->count(),
            'active_jobs' => JobListing::where('mitra_id', $mitra->id)
                ->where('status', 'published')
                ->count(),
            'pending_jobs' => JobListing::where('mitra_id', $mitra->id)
                ->where('status', 'pending_review')
                ->count(),
            'total_applicants' => JobApplycants::whereHas('jobListing', fn($q) => $q->where('mitra_id', $mitra->id))
                ->count(),
            'pending_applicants' => JobApplycants::whereHas('jobListing', fn($q) => $q->where('mitra_id', $mitra->id))
                ->where('status', 'applied')
                ->count(),
        ];

        $recentApplicants = JobApplycants::whereHas('jobListing', fn($q) => $q->where('mitra_id', $mitra->id))
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
