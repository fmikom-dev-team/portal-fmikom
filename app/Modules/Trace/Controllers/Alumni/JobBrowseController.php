<?php

namespace App\Modules\Trace\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\Pagi\PagiCv;
use App\Models\Pagi\PagiWork;
use App\Models\Tracer\Bookmark;
use App\Models\Tracer\JobApplicant;
use App\Models\Tracer\JobCategory;
use App\Models\Tracer\JobListing;
use App\Models\Tracer\MitraProfile;
use App\Models\Tracer\ProfilAlumni;
use App\Models\User;
use App\Modules\Trace\Actions\ApplyToJobAction;
use App\Notifications\Trace\JobApplicationConfirmation;
use App\Notifications\Trace\JobApplicationSubmitted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class JobBrowseController extends Controller
{
    public function index(Request $request): InertiaResponse
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
            $search = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('mitra', function ($mq) use ($search) {
                        $mq->where('nama_perusahaan', 'like', "%{$search}%");
                    });
            });
        }

        $jobs = $query->latest()->paginate(12)->withQueryString();
        $bookmarkedJobIds = Bookmark::where('user_id', auth()->id())
            ->whereIn('job_id', $jobs->getCollection()->pluck('id'))
            ->pluck('job_id');

        $jobs->getCollection()->transform(function (JobListing $job) use ($bookmarkedJobIds) {
            $job->setAttribute('is_bookmarked', $bookmarkedJobIds->contains($job->id));

            return $job;
        });

        $categories = Cache::remember('trace_job_categories', now()->addHours(1), function () {
            return JobCategory::select('id', 'nama')->get();
        });

        // Get only mitras that have published jobs
        $mitras = MitraProfile::whereHas('jobListings', function ($q) {
            $q->where('status', 'published');
        })->select('id', 'nama_perusahaan')->orderBy('nama_perusahaan')->get();

        return Inertia::render('Modules/Trace/Alumni/Jobs/Index', [
            'jobs' => $jobs,
            'categories' => $categories,
            'mitras' => $mitras,
            'filters' => $request->only(['category', 'tipe_kerja', 'location_type', 'search', 'mitra_id', 'salary_min', 'salary_max']),
        ]);
    }

    public function show($id): InertiaResponse
    {
        $job = JobListing::where('status', 'published')
            ->with(['mitra', 'category'])
            ->findOrFail($id);

        $alumniProfile = ProfilAlumni::where('user_id', auth()->id())->first();

        $hasApplied = false;
        $myApplication = null;
        if ($alumniProfile) {
            $myApplication = JobApplicant::where('job_id', $id)
                ->where('alumni_id', $alumniProfile->id)
                ->first();
            $hasApplied = $myApplication !== null;
        }

        $isBookmarked = Bookmark::where('job_id', $id)
            ->where('user_id', auth()->id())
            ->exists();

        $myCvs = PagiCv::where('user_id', auth()->id())->select('id', 'title')->get();
        $myWorks = PagiWork::where('user_id', auth()->id())->select('id', 'title')->get();

        return Inertia::render('Modules/Trace/Alumni/Jobs/Show', [
            'job' => $job,
            'hasApplied' => $hasApplied,
            'myApplication' => $myApplication,
            'isBookmarked' => $isBookmarked,
            'myCvs' => $myCvs,
            'myWorks' => $myWorks,
        ]);
    }

    public function apply(Request $request, $id, ApplyToJobAction $action): RedirectResponse
    {
        $request->validate([
            'cover_letter' => 'nullable|string',
            'attached_cv_ids' => 'nullable|array',
            'attached_cv_ids.*' => 'integer',
            'attached_portfolio_ids' => 'nullable|array',
            'attached_portfolio_ids.*' => 'integer',
        ]);

        $alumniProfile = ProfilAlumni::where('user_id', auth()->id())->first();

        if (! $alumniProfile) {
            return redirect()->back()->with('error', 'Profil alumni belum lengkap. Silakan lengkapi profil terlebih dahulu.');
        }

        $job = JobListing::where('status', 'published')->findOrFail($id);

        $result = $action->execute($job, $alumniProfile, $request->only([
            'cover_letter',
            'attached_cv_ids',
            'attached_portfolio_ids',
        ]), auth()->id());

        if (isset($result['error'])) {
            return redirect()->back()->with('error', $result['error']);
        }

        // Notify mitra about new application
        $mitraUser = $job->mitra?->user;
        if ($mitraUser) {
            $mitraUser->notify(new JobApplicationSubmitted(
                auth()->user()->name,
                $job->title,
                $job->id,
            ));
        }

        // Notify admins
        $admins = User::where('user_type', 'admin')->get();
        Notification::send($admins, new JobApplicationSubmitted(
            auth()->user()->name,
            $job->title,
            $job->id,
        ));

        // Notify applicant (alumni)
        $companyName = $job->mitra->nama_perusahaan ?? 'Mitra FMIKOM';
        auth()->user()->notify(new JobApplicationConfirmation(
            $job->title,
            $companyName,
            $job->id
        ));

        return redirect()->back()->with('success', 'Lamaran berhasil dikirim.');
    }

    public function toggleBookmark(Request $request, $id): RedirectResponse|JsonResponse
    {
        $job = JobListing::where('status', 'published')->findOrFail($id);

        $bookmark = Bookmark::where('user_id', auth()->id())
            ->where('job_id', $job->id)
            ->first();

        if ($bookmark) {
            $bookmark->delete();
            $bookmarked = false;
        } else {
            Bookmark::create([
                'user_id' => auth()->id(),
                'job_id' => $job->id,
            ]);
            $bookmarked = true;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'bookmarked' => $bookmarked,
            ]);
        }

        return redirect()->back();
    }

    public function myApplications(Request $request): InertiaResponse|RedirectResponse
    {
        $alumniProfile = ProfilAlumni::where('user_id', auth()->id())->first();

        if (! $alumniProfile) {
            return redirect()->back()->with('error', 'Profil alumni belum lengkap. Silakan lengkapi profil terlebih dahulu.');
        }

        $applications = JobApplicant::where('alumni_id', $alumniProfile->id)
            ->whereHas('jobListing')
            ->with(['jobListing.mitra'])
            ->latest()
            ->paginate(12);

        return Inertia::render('Modules/Trace/Alumni/Jobs/MyApplications', [
            'applications' => $applications,
        ]);
    }

    public function myBookmarks(Request $request): InertiaResponse
    {
        $bookmarks = Bookmark::where('user_id', auth()->id())
            ->with(['jobListing' => function ($query) {
                $query->withTrashed()->with(['mitra', 'category']);
            }])
            ->latest()
            ->paginate(12);

        return Inertia::render('Modules/Trace/Alumni/Jobs/MyBookmarks', [
            'bookmarks' => $bookmarks,
        ]);
    }

    public function companies(Request $request): InertiaResponse
    {
        $query = MitraProfile::withCount(['jobListings' => function ($q) {
            $q->where('status', 'published');
        }]);

        // Search
        if ($request->filled('search')) {
            $search = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $request->input('search'));
            $query->where('nama_perusahaan', 'like', "%{$search}%");
        }

        $companies = $query->orderByDesc('job_listings_count')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Modules/Trace/Alumni/Jobs/Companies', [
            'companies' => $companies,
            'filters' => [
                'search' => $request->input('search'),
            ],
        ]);
    }
}
