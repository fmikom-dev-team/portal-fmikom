<?php

namespace App\Modules\Trace\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\Tracer\Bookmarks;
use App\Models\Tracer\JobApplycants;
use App\Models\Tracer\JobCategory;
use App\Models\Tracer\JobListing;
use App\Models\Tracer\ProfilAlumni;
use App\Models\Pagi\PagiCv;
use App\Models\Pagi\PagiWork;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Notifications\Trace\JobApplicationSubmitted;
use App\Models\Tracer\ActivityLog;

class JobBrowseController extends Controller
{
    public function index(Request $request)
    {
        $query = JobListing::where('status', 'published')
            ->with(['category', 'mitra'])
            ->withCount('applicants');

        if ($request->filled('category')) {
            $query->where('job_category_id', $request->category);
        }

        if ($request->filled('tipe_kerja')) {
            $query->where('tipe_kerja', $request->tipe_kerja);
        }

        if ($request->filled('location_type')) {
            $query->where('location_type', $request->location_type);
        }

        if ($request->filled('mitra_id')) {
            $query->where('mitra_id', $request->mitra_id);
        }

        if ($request->filled('salary_min')) {
            $query->where(function ($q) use ($request) {
                $q->where('salary_max', '>=', $request->salary_min)
                  ->orWhere('salary_min', '>=', $request->salary_min);
            });
        }

        if ($request->filled('salary_max')) {
            $query->where(function ($q) use ($request) {
                $q->where('salary_min', '<=', $request->salary_max)
                  ->orWhereNull('salary_min');
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('mitra', function ($mq) use ($search) {
                        $mq->where('nama_perusahaan', 'like', "%{$search}%");
                    });
            });
        }

        $jobs = $query->latest()->paginate(12)->withQueryString();

        $categories = JobCategory::all();

        // Get only mitras that have published jobs
        $mitras = \App\Models\Tracer\MitraProfiles::whereHas('jobListings', function ($q) {
            $q->where('status', 'published');
        })->select('id', 'nama_perusahaan')->orderBy('nama_perusahaan')->get();

        return Inertia::render('Modules/Trace/Alumni/Jobs/Index', [
            'jobs' => $jobs,
            'categories' => $categories,
            'mitras' => $mitras,
            'filters' => $request->only(['category', 'tipe_kerja', 'location_type', 'search', 'mitra_id', 'salary_min', 'salary_max']),
        ]);
    }

    public function show($id)
    {
        $job = JobListing::where('status', 'published')
            ->with(['mitra', 'category'])
            ->findOrFail($id);

        $alumniProfile = ProfilAlumni::where('user_id', auth()->id())->first();

        $hasApplied = false;
        $myApplication = null;
        if ($alumniProfile) {
            $myApplication = JobApplycants::where('job_id', $id)
                ->where('alumni_id', $alumniProfile->id)
                ->first();
            $hasApplied = $myApplication !== null;
        }

        $isBookmarked = Bookmarks::where('job_id', $id)
            ->where('user_id', auth()->id())
            ->exists();

        $myCvs = PagiCv::where('user_id', auth()->id())->get();
        $myWorks = PagiWork::where('user_id', auth()->id())->get();

        return Inertia::render('Modules/Trace/Alumni/Jobs/Show', [
            'job' => $job,
            'hasApplied' => $hasApplied,
            'myApplication' => $myApplication,
            'isBookmarked' => $isBookmarked,
            'myCvs' => $myCvs,
            'myWorks' => $myWorks,
        ]);
    }

    public function apply(Request $request, $id)
    {
        $request->validate([
            'cover_letter' => 'nullable|string',
            'attached_cv_ids' => 'nullable|array',
            'attached_cv_ids.*' => 'integer',
            'attached_portfolio_ids' => 'nullable|array',
            'attached_portfolio_ids.*' => 'integer',
        ]);

        $alumniProfile = ProfilAlumni::where('user_id', auth()->id())->first();

        if (!$alumniProfile) {
            return redirect()->back()->with('error', 'Profil alumni belum lengkap. Silakan lengkapi profil terlebih dahulu.');
        }

        $job = JobListing::where('status', 'published')->findOrFail($id);

        // Check if deadline has passed
        if ($job->deadline && now()->greaterThan($job->deadline)) {
            return redirect()->back()->with('error', 'Batas waktu lamaran sudah berakhir.');
        }

        // Validate that selected CVs belong to the current user
        $cvIds = $request->attached_cv_ids ?? [];
        if (!empty($cvIds)) {
            $validCvCount = PagiCv::where('user_id', auth()->id())
                ->whereIn('id', $cvIds)
                ->count();
            if ($validCvCount !== count($cvIds)) {
                return redirect()->back()->with('error', 'CV yang dipilih tidak valid.');
            }
        }

        // Validate that selected portfolios belong to the current user
        $portfolioIds = $request->attached_portfolio_ids ?? [];
        if (!empty($portfolioIds)) {
            $validWorkCount = PagiWork::where('user_id', auth()->id())
                ->whereIn('id', $portfolioIds)
                ->count();
            if ($validWorkCount !== count($portfolioIds)) {
                return redirect()->back()->with('error', 'Portfolio yang dipilih tidak valid.');
            }
        }

        return DB::transaction(function () use ($request, $job, $alumniProfile, $cvIds, $portfolioIds) {
            $alreadyApplied = JobApplycants::where('job_id', $job->id)
                ->where('alumni_id', $alumniProfile->id)
                ->lockForUpdate()
                ->exists();

            if ($alreadyApplied) {
                return redirect()->back()->with('error', 'Anda sudah melamar pada lowongan ini.');
            }

            JobApplycants::create([
                'job_id' => $job->id,
                'alumni_id' => $alumniProfile->id,
                'cover_letter' => $request->cover_letter,
                'attached_cv_ids' => !empty($cvIds) ? $cvIds : null,
                'attached_portfolio_ids' => !empty($portfolioIds) ? $portfolioIds : null,
                'status' => 'applied',
                'applied_at' => now(),
            ]);

            // Notify mitra about new application
            $mitraUser = $job->mitra?->user;
            if ($mitraUser) {
                $mitraUser->notify(new JobApplicationSubmitted(
                    auth()->user()->name,
                    $job->title,
                    $job->id,
                ));
            }

            ActivityLog::record('job.applied', "Melamar lowongan: {$job->title}", $job);

            return redirect()->back()->with('success', 'Lamaran berhasil dikirim.');
        });
    }

    public function toggleBookmark($id)
    {
        $job = JobListing::where('status', 'published')->findOrFail($id);

        $bookmark = Bookmarks::where('user_id', auth()->id())
            ->where('job_id', $job->id)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            ActivityLog::record('job.unbookmarked', "Menghapus simpanan lowongan: {$job->title}", $job);
        } else {
            Bookmarks::create([
                'user_id' => auth()->id(),
                'job_id' => $job->id,
            ]);
            ActivityLog::record('job.bookmarked', "Menyimpan lowongan: {$job->title}", $job);
        }

        return redirect()->back();
    }

    public function myApplications(Request $request)
    {
        $alumniProfile = ProfilAlumni::where('user_id', auth()->id())->first();

        if (!$alumniProfile) {
            return redirect()->back()->with('error', 'Profil alumni belum lengkap. Silakan lengkapi profil terlebih dahulu.');
        }

        $applications = JobApplycants::where('alumni_id', $alumniProfile->id)
            ->whereHas('jobListing')
            ->with(['jobListing.mitra'])
            ->latest()
            ->paginate(12);

        return Inertia::render('Modules/Trace/Alumni/Jobs/MyApplications', [
            'applications' => $applications,
        ]);
    }

    public function myBookmarks(Request $request)
    {
        $bookmarks = Bookmarks::where('user_id', auth()->id())
            ->with(['jobListing' => function ($query) {
                $query->withTrashed()->with(['mitra', 'category']);
            }])
            ->latest()
            ->paginate(12);

        return Inertia::render('Modules/Trace/Alumni/Jobs/MyBookmarks', [
            'bookmarks' => $bookmarks,
        ]);
    }

    public function companies(Request $request)
    {
        $query = \App\Models\Tracer\MitraProfiles::withCount(['jobListings' => function ($q) {
            $q->where('status', 'published');
        }]);

        // Search
        if ($search = $request->input('search')) {
            $query->where('nama_perusahaan', 'like', "%{$search}%");
        }

        $companies = $query->orderByDesc('job_listings_count')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Modules/Trace/Alumni/Jobs/Companies', [
            'companies' => $companies,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }
}
