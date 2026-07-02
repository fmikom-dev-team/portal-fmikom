<?php

namespace App\Modules\Trace\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trace\StoreJobRequest;
use App\Http\Requests\Trace\UpdateJobRequest;
use App\Models\Tracer\JobApplicant;
use App\Models\Tracer\JobCategory;
use App\Models\Tracer\JobListing;
use App\Models\Tracer\MitraProfile;
use App\Models\User;
use App\Modules\Trace\Services\ImageService;
use App\Notifications\Trace\ApplicationStatusChanged;
use App\Notifications\Trace\JobApprovedForMitra;
use App\Notifications\Trace\JobRejectedForMitra;
use App\Notifications\Trace\NewJobPosted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class JobManagementController extends Controller
{
    /**
     * Determine if the current admin user owns the given job.
     * Admin owns a job when they created it (user_id) and it has no mitra owner.
     */
    private function isOwner(JobListing $job): bool
    {
        return $job->user_id === auth()->id() && $job->mitra_id === null;
    }

    public function index(Request $request): InertiaResponse
    {
        $query = JobListing::with(['mitra', 'category'])
            ->withCount('applicants')
            ->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('job_category_id', $request->category_id);
        }

        // Search by title or mitra company name
        if ($request->filled('search')) {
            $search = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('mitra', function ($mq) use ($search) {
                        $mq->where('nama_perusahaan', 'like', "%{$search}%");
                    });
            });
        }

        $jobs = $query->paginate(15)->withQueryString();
        $categories = JobCategory::select('id', 'nama')->get();

        $stats = Cache::remember('trace_job_stats', now()->addMinutes(5), function () {
            return [
                'total' => JobListing::count(),
                'pending_review' => JobListing::where('status', 'pending_review')->count(),
                'published' => JobListing::where('status', 'published')->count(),
            ];
        });

        return Inertia::render('Modules/Trace/Admin/Jobs/Index', [
            'jobs' => $jobs,
            'stats' => $stats,
            'categories' => $categories,
            'filters' => $request->only(['status', 'search', 'category_id']),
        ]);
    }

    public function show($id): InertiaResponse
    {
        $job = JobListing::with(['mitra', 'category', 'creator'])
            ->withCount('applicants')
            ->findOrFail($id);

        $isOwner = $this->isOwner($job);

        $applicants = JobApplicant::where('job_id', $job->id)
            ->with(['alumni.user.pagiCvs', 'alumni.user.pagiWorks'])
            ->paginate(15);

        return Inertia::render('Modules/Trace/Admin/Jobs/Show', [
            'job' => $job,
            'applicants' => $applicants,
            'isOwner' => $isOwner,
        ]);
    }

    public function create(): InertiaResponse
    {
        $categories = JobCategory::select('id', 'nama')->get();
        $mitras = MitraProfile::select('id', 'nama_perusahaan')->get();

        return Inertia::render('Modules/Trace/Admin/Jobs/Create', [
            'categories' => $categories,
            'mitras' => $mitras,
        ]);
    }

    public function store(StoreJobRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('poster')) {
            $validated['poster_path'] = ImageService::compressToWebp(
                $request->file('poster'), 'job-posters', quality: 80, maxWidth: 1200
            );
        }

        $validated['user_id'] = auth()->id();
        $validated['status'] = $validated['status'] ?? 'published';

        $job = JobListing::create($validated);
        if ($job->status === 'published') {
            $companyName = $job->mitra?->nama_perusahaan ?? 'Admin FMIKOM';
            User::whereHas('alumniProfile')->select('id', 'name', 'email')->chunkById(200, function ($alumni) use ($job, $companyName) {
                Notification::send($alumni, new NewJobPosted($job->title, $companyName, $job->id));
            });
        }

        Cache::forget('trace_job_stats');

        return redirect()->route('module.trace.admin.jobs')
            ->with('success', 'Lowongan berhasil dibuat.');
    }

    public function edit($id): InertiaResponse
    {
        $job = JobListing::with('mitra', 'category')->findOrFail($id);
        $categories = JobCategory::select('id', 'nama')->get();
        $mitras = MitraProfile::select('id', 'nama_perusahaan')->get();

        return Inertia::render('Modules/Trace/Admin/Jobs/Edit', [
            'job' => $job,
            'categories' => $categories,
            'mitras' => $mitras,
        ]);
    }

    public function update(UpdateJobRequest $request, $id): RedirectResponse
    {
        $job = JobListing::findOrFail($id);
        $validated = $request->validated();

        if ($request->hasFile('poster')) {
            $validated['poster_path'] = ImageService::replaceWithWebp(
                $request->file('poster'), $job->poster_path, 'job-posters', quality: 80, maxWidth: 1200
            );
        }

        $job->update($validated);
        Cache::forget('trace_job_stats');

        return redirect()->route('module.trace.admin.jobs.show', $job->id)
            ->with('success', 'Lowongan berhasil diperbarui.');
    }

    // ── Moderation (all jobs) ───────────────────────────────────────────

    public function approve($id): RedirectResponse
    {
        $job = JobListing::with('mitra.user')->where('status', 'pending_review')->findOrFail($id);

        $job->update(['status' => 'published']);
        // Notify mitra that their job was approved
        $mitraUser = $job->mitra?->user;
        if ($mitraUser) {
            $mitraUser->notify(new JobApprovedForMitra($job->title, $job->id));
        }

        // Notify all alumni about the new job (chunked to avoid memory issues)
        $companyName = $job->mitra?->nama_perusahaan ?? 'Admin FMIKOM';
        User::whereHas('alumniProfile')
            ->select('id', 'name', 'email')
            ->chunkById(200, function ($alumni) use ($job, $companyName) {
                Notification::send($alumni, new NewJobPosted($job->title, $companyName, $job->id));
            });

        Cache::forget('trace_job_stats');

        return back()->with('success', 'Lowongan berhasil disetujui dan dipublikasikan.');
    }

    public function reject(Request $request, $id): RedirectResponse
    {
        $job = JobListing::with('mitra.user')->where('status', 'pending_review')->findOrFail($id);

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $reason = $validated['rejection_reason'] ?? '';

        $job->update([
            'status' => 'rejected',
            'rejection_reason' => $reason ?: null,
            'rejected_at' => now(),
        ]);
        // Notify mitra that their job was rejected
        $mitraUser = $job->mitra?->user;
        if ($mitraUser) {
            $mitraUser->notify(new JobRejectedForMitra($job->title, $job->id, $reason ?: null));
        }

        Cache::forget('trace_job_stats');

        return back()->with('success', 'Lowongan berhasil ditolak.'.($reason ? " Alasan: {$reason}" : ''));
    }

    public function bulkApprove(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:jobs_listings,id',
        ]);

        $jobs = JobListing::with('mitra.user')
            ->whereIn('id', $validated['ids'])
            ->where('status', 'pending_review')
            ->get();

        // Update statuses within a transaction for data integrity
        DB::transaction(function () use ($jobs) {
            foreach ($jobs as $job) {
                $job->update(['status' => 'published']);
            }
        });

        // Send notifications AFTER transaction commits (outside transaction)
        foreach ($jobs as $job) {
            $mitraUser = $job->mitra?->user;
            if ($mitraUser) {
                $mitraUser->notify(new JobApprovedForMitra($job->title, $job->id));
            }
        }

        // Notify alumni for each approved job (chunked to avoid memory issues)
        foreach ($jobs as $job) {
            $companyName = $job->mitra?->nama_perusahaan ?? 'Admin FMIKOM';
            User::whereHas('alumniProfile')
                ->select('id', 'name', 'email')
                ->chunkById(200, function ($alumni) use ($job, $companyName) {
                    Notification::send($alumni, new NewJobPosted($job->title, $companyName, $job->id));
                });
        }

        Cache::forget('trace_job_stats');

        return back()->with('success', count($jobs).' lowongan berhasil disetujui.');
    }

    public function bulkReject(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:jobs_listings,id',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $reason = $validated['rejection_reason'] ?? '';
        $jobs = JobListing::with('mitra.user')
            ->whereIn('id', $validated['ids'])
            ->where('status', 'pending_review')
            ->get();

        DB::transaction(function () use ($jobs, $reason) {
            foreach ($jobs as $job) {
                $job->update([
                    'status' => 'rejected',
                    'rejection_reason' => $reason ?: null,
                    'rejected_at' => now(),
                ]);
            }
        });

        // Notifications AFTER transaction commits
        foreach ($jobs as $job) {
            $mitraUser = $job->mitra?->user;
            if ($mitraUser) {
                $mitraUser->notify(new JobRejectedForMitra($job->title, $job->id, $reason ?: null));
            }
        }

        Cache::forget('trace_job_stats');

        return back()->with('success', count($jobs).' lowongan berhasil ditolak.');
    }

    public function destroy($id): RedirectResponse
    {
        $job = JobListing::findOrFail($id);
        $posterPath = $job->poster_path;

        $job->delete();

        if ($posterPath) {
            Storage::disk('public')->delete($posterPath);
        }

        Cache::forget('trace_job_stats');

        return redirect()->route('module.trace.admin.jobs')
            ->with('success', 'Lowongan berhasil dihapus.');
    }

    // ── Applicant Management (owner only) ───────────────────────────────

    public function updateApplicantStatus(Request $request, $jobId, $applicantId): RedirectResponse
    {
        $job = JobListing::findOrFail($jobId);

        // Only the job owner can manage applicants
        if (! $this->isOwner($job)) {
            abort(403, 'Anda tidak memiliki akses untuk mengelola pelamar lowongan ini.');
        }

        $validated = $request->validate([
            'status' => 'required|in:applied,reviewed,accepted,rejected',
            'note' => 'nullable|string|max:1000',
        ]);

        $applicant = JobApplicant::where('job_id', $job->id)->findOrFail($applicantId);

        $updateData = [
            'status' => $validated['status'],
        ];

        if (in_array($validated['status'], ['accepted', 'rejected'])) {
            $updateData['reviewer_note'] = $validated['note'] ?? null;
            $updateData['reviewed_at'] = now();
        }

        $applicant->update($updateData);

        // Notify alumni about status change
        $alumniUser = $applicant->alumni?->user;
        if ($alumniUser) {
            $companyName = $job->mitra?->nama_perusahaan ?? 'Admin FMIKOM';
            $alumniUser->notify(new ApplicationStatusChanged(
                $job->title,
                $validated['status'],
                $companyName,
                $job->id,
            ));
        }

        return back()->with('success', 'Status pelamar berhasil diperbarui.');
    }
}
