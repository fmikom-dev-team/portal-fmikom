<?php

namespace App\Modules\Trace\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tracer\JobApplicant;
use App\Models\Tracer\JobCategory;
use App\Models\Tracer\JobListing;
use App\Models\Tracer\MitraProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Notifications\Trace\ApplicationStatusChanged;
use App\Notifications\Trace\NewJobPosted;
use App\Notifications\Trace\JobApprovedForMitra;
use App\Notifications\Trace\JobRejectedForMitra;
use App\Models\Tracer\ActivityLog;
use Illuminate\Support\Facades\Notification;

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

    public function index(Request $request)
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
        $categories = JobCategory::all();

        $stats = [
            'total' => JobListing::count(),
            'pending_review' => JobListing::where('status', 'pending_review')->count(),
            'published' => JobListing::where('status', 'published')->count(),
        ];

        return Inertia::render('Modules/Trace/Admin/Jobs/Index', [
            'jobs' => $jobs,
            'stats' => $stats,
            'categories' => $categories,
            'filters' => $request->only(['status', 'search', 'category_id']),
        ]);
    }

    public function show($id)
    {
        $job = JobListing::with(['mitra', 'category', 'creator'])
            ->withCount('applicants')
            ->findOrFail($id);

        $isOwner = $this->isOwner($job);

        $applicants = JobApplicant::where('job_id', $job->id)
            ->with(['alumni.user.pagiCvs', 'alumni.user.pagiWorks'])
            ->get();

        return Inertia::render('Modules/Trace/Admin/Jobs/Show', [
            'job' => $job,
            'applicants' => $applicants,
            'isOwner' => $isOwner,
        ]);
    }

    public function create()
    {
        $categories = JobCategory::all();
        $mitras = MitraProfile::all();

        return Inertia::render('Modules/Trace/Admin/Jobs/Create', [
            'categories' => $categories,
            'mitras' => $mitras,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->validationRules());

        $validated['user_id'] = auth()->id();
        $validated['mitra_id'] = $request->input('mitra_id');
        $validated['status'] = $validated['status'] ?? 'published';

        $job = JobListing::create($validated);
        ActivityLog::record('job.created', "Membuat lowongan: {$job->title}", $job);

        if ($job->status === 'published') {
            $companyName = $job->mitra?->nama_perusahaan ?? 'Admin FMIKOM';
            $alumni = User::whereHas('alumniProfile')->get();
            Notification::send($alumni, new NewJobPosted($job->title, $companyName, $job->id));
        }

        return redirect()->route('module.trace.admin.jobs')
            ->with('success', 'Lowongan berhasil dibuat.');
    }

    public function edit($id)
    {
        $job = JobListing::with('mitra', 'category')->findOrFail($id);
        $categories = JobCategory::all();
        $mitras = MitraProfile::all();

        return Inertia::render('Modules/Trace/Admin/Jobs/Edit', [
            'job' => $job,
            'categories' => $categories,
            'mitras' => $mitras,
        ]);
    }

    public function update(Request $request, $id)
    {
        $job = JobListing::findOrFail($id);
        $validated = $request->validate($this->validationRules());

        if ($request->has('mitra_id')) {
            $validated['mitra_id'] = $request->input('mitra_id');
        }

        $job->update($validated);
        ActivityLog::record('job.updated', "Memperbarui lowongan: {$job->title}", $job);

        return redirect()->route('module.trace.admin.jobs.show', $job->id)
            ->with('success', 'Lowongan berhasil diperbarui.');
    }

    // ── Moderation (all jobs) ───────────────────────────────────────────

    public function approve($id)
    {
        $job = JobListing::with('mitra.user')->where('status', 'pending_review')->findOrFail($id);

        $job->update(['status' => 'published']);
        ActivityLog::record('job.approved', "Menyetujui lowongan: {$job->title}", $job);

        // Notify mitra that their job was approved
        $mitraUser = $job->mitra?->user;
        if ($mitraUser) {
            $mitraUser->notify(new JobApprovedForMitra($job->title, $job->id));
        }

        // Notify all alumni about the new job
        $companyName = $job->mitra?->nama_perusahaan ?? 'Admin FMIKOM';
        $alumni = User::whereHas('alumniProfile')->get();
        Notification::send($alumni, new NewJobPosted($job->title, $companyName, $job->id));

        return back()->with('success', 'Lowongan berhasil disetujui dan dipublikasikan.');
    }

    public function reject(Request $request, $id)
    {
        $job = JobListing::with('mitra.user')->where('status', 'pending_review')->findOrFail($id);

        $reason = $request->input('rejection_reason', '');

        $job->update([
            'status' => 'rejected',
            'rejection_reason' => $reason ?: null,
            'rejected_at' => now(),
        ]);
        ActivityLog::record('job.rejected', "Menolak lowongan: {$job->title}", $job, ['reason' => $reason]);

        // Notify mitra that their job was rejected
        $mitraUser = $job->mitra?->user;
        if ($mitraUser) {
            $mitraUser->notify(new JobRejectedForMitra($job->title, $job->id, $reason ?: null));
        }

        return back()->with('success', 'Lowongan berhasil ditolak.' . ($reason ? " Alasan: {$reason}" : ''));
    }

    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:jobs_listings,id',
        ]);

        $jobs = JobListing::whereIn('id', $validated['ids'])
            ->where('status', 'pending_review')
            ->get();

        foreach ($jobs as $job) {
            $job->update(['status' => 'published']);

            // Notify mitra
            $mitraUser = $job->mitra?->user;
            if ($mitraUser) {
                $mitraUser->notify(new JobApprovedForMitra($job->title, $job->id));
            }

            ActivityLog::record('job.approved', "Menyetujui lowongan: {$job->title}", $job);

            // Notify alumni about new job
            $companyName = $job->mitra?->nama_perusahaan ?? 'Admin FMIKOM';
            $alumni = User::whereHas('alumniProfile')->get();
            Notification::send($alumni, new NewJobPosted($job->title, $companyName, $job->id));
        }

        return back()->with('success', count($jobs) . ' lowongan berhasil disetujui.');
    }

    public function bulkReject(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:jobs_listings,id',
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $reason = $validated['rejection_reason'] ?? '';
        $jobs = JobListing::whereIn('id', $validated['ids'])
            ->where('status', 'pending_review')
            ->get();

        foreach ($jobs as $job) {
            $job->update([
                'status' => 'rejected',
                'rejection_reason' => $reason ?: null,
                'rejected_at' => now(),
            ]);

            $mitraUser = $job->mitra?->user;
            if ($mitraUser) {
                $mitraUser->notify(new JobRejectedForMitra($job->title, $job->id, $reason ?: null));
            }

            ActivityLog::record('job.rejected', "Menolak lowongan: {$job->title}", $job, ['reason' => $reason]);
        }

        return back()->with('success', count($jobs) . ' lowongan berhasil ditolak.');
    }

    public function destroy($id)
    {
        $job = JobListing::findOrFail($id);

        ActivityLog::record('job.deleted', "Menghapus lowongan: {$job->title}", $job);
        $job->delete();

        return redirect()->route('module.trace.admin.jobs')
            ->with('success', 'Lowongan berhasil dihapus.');
    }

    // ── Applicant Management (owner only) ───────────────────────────────

    public function updateApplicantStatus(Request $request, $jobId, $applicantId)
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

    private function validationRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:65535',
            'job_category_id' => 'nullable|exists:job_categories,id',
            'experience_level' => 'required|in:fresh_graduate,junior,mid_level,senior,internship',
            'location_type' => 'required|in:onsite,remote,hybrid',
            'location_city' => 'nullable|string',
            'tipe_kerja' => 'required|in:full_time,part_time,magang,freelance',
            'salary_min' => 'nullable|integer|min:0',
            'salary_max' => 'nullable|integer|min:0|gte:salary_min',
            'deadline' => 'nullable|date|after:today',
            'is_salary_visible' => 'boolean',
            'status' => 'in:draft,published,closed',
            'mitra_id' => 'nullable|exists:mitra_profiles,id',
        ];
    }
}
