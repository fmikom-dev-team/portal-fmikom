<?php

namespace App\Modules\Trace\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Tracer\JobApplycants;
use App\Models\Tracer\JobCategory;
use App\Models\Tracer\JobListing;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Notifications\Trace\ApplicationStatusChanged;
use App\Notifications\Trace\JobSubmittedForReview;
use App\Models\Tracer\ActivityLog;

class JobListingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $mitra = $user->mitraProfile;

        if (!$mitra) {
            return redirect()->route('module.trace.open-job.mitra-profile-setup');
        }

        $jobs = JobListing::where('mitra_id', $mitra->id)
            ->with('category')
            ->withCount('applicants')
            ->latest()
            ->paginate(10);

        return Inertia::render('Modules/Trace/Mitra/Jobs/Index', [
            'jobs' => $jobs,
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $mitra = $user->mitraProfile;

        if (!$mitra) {
            return redirect()->route('module.trace.open-job.mitra-profile-setup');
        }

        $categories = JobCategory::all();

        return Inertia::render('Modules/Trace/Mitra/Jobs/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $mitra = $user->mitraProfile;

        if (!$mitra) {
            return redirect()->route('module.trace.open-job.mitra-profile-setup');
        }

        $validated = $request->validate($this->validationRules());

        $validated['user_id'] = $user->id;
        $validated['mitra_id'] = $mitra->id;
        $validated['status'] = $validated['status'] ?? 'draft';

        // Mitra cannot publish directly — redirect to pending_review for admin approval
        if ($validated['status'] === 'published') {
            $validated['status'] = 'pending_review';
        }

        $job = JobListing::create($validated);
        ActivityLog::record('job.created_by_mitra', "Mitra membuat lowongan: {$job->title}", $job);

        return redirect()->route('module.trace.open-job.jobs-listings')
            ->with('success', 'Lowongan berhasil dibuat.');
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $mitra = $user->mitraProfile;

        if (!$mitra) {
            return redirect()->route('module.trace.open-job.mitra-profile-setup');
        }

        $job = JobListing::where('mitra_id', $mitra->id)
            ->with(['category', 'mitra'])
            ->withCount('applicants')
            ->findOrFail($id);

        // Load applicants with alumni profile, user, and PaGI data
        // Note: User model has pagiWorks() relationship.
        // TODO: Add pagiCvs() relationship to User model when PaGI CV feature is integrated.
        $applicants = JobApplycants::where('job_id', $job->id)
            ->with(['alumni.user.pagiWorks', 'alumni.user.pagiCvs'])
            ->get();

        $categories = JobCategory::all();

        return Inertia::render('Modules/Trace/Mitra/Jobs/Show', [
            'job' => $job,
            'applicants' => $applicants,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $mitra = $user->mitraProfile;

        if (!$mitra) {
            return redirect()->route('module.trace.open-job.mitra-profile-setup');
        }

        $job = JobListing::where('mitra_id', $mitra->id)->findOrFail($id);

        $validated = $request->validate($this->validationRules());

        // Mitra cannot publish directly — redirect to pending_review for admin approval
        if (isset($validated['status']) && $validated['status'] === 'published') {
            $validated['status'] = 'pending_review';
        }

        $job->update($validated);

        return back()->with('success', 'Lowongan berhasil diperbarui.');
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $mitra = $user->mitraProfile;

        if (!$mitra) {
            return redirect()->route('module.trace.open-job.mitra-profile-setup');
        }

        $job = JobListing::where('mitra_id', $mitra->id)->findOrFail($id);

        $job->delete(); // SoftDeletes

        return redirect()->route('module.trace.open-job.jobs-listings')
            ->with('success', 'Lowongan berhasil dihapus.');
    }

    public function updateApplicantStatus(Request $request, $jobId, $applicantId)
    {
        $user = $request->user();
        $mitra = $user->mitraProfile;

        if (!$mitra) {
            return redirect()->route('module.trace.open-job.mitra-profile-setup');
        }

        // Ensure the job belongs to this mitra
        $job = JobListing::where('mitra_id', $mitra->id)->findOrFail($jobId);

        $validated = $request->validate([
            'status' => 'required|in:applied,reviewed,accepted,rejected',
            'note' => 'nullable|string|max:1000',
        ]);

        $applicant = JobApplycants::where('job_id', $job->id)->findOrFail($applicantId);

        $updateData = [
            'status' => $validated['status'],
        ];

        // Save reviewer note and timestamp when accepting or rejecting
        if (in_array($validated['status'], ['accepted', 'rejected'])) {
            $updateData['reviewer_note'] = $validated['note'] ?? null;
            $updateData['reviewed_at'] = now();
        }

        $applicant->update($updateData);
        ActivityLog::record('applicant.status_changed', "Mengubah status pelamar di lowongan: {$job->title} → {$validated['status']}", $applicant, ['status' => $validated['status']]);

        // Notify alumni about status change
        $alumniUser = $applicant->alumni?->user;
        if ($alumniUser) {
            $mitra = $request->user()->mitraProfile;
            $alumniUser->notify(new ApplicationStatusChanged(
                $job->title,
                $validated['status'],
                $mitra->nama_perusahaan ?? 'Perusahaan',
                $job->id,
            ));
        }

        return back()->with('success', 'Status pelamar berhasil diperbarui.');
    }

    public function submitForReview(Request $request, $id)
    {
        $user = $request->user();
        $mitra = $user->mitraProfile;

        if (!$mitra) {
            return redirect()->route('module.trace.open-job.mitra-profile-setup');
        }

        $job = JobListing::where('mitra_id', $mitra->id)->findOrFail($id);

        if (!in_array($job->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Hanya lowongan berstatus draft atau ditolak yang dapat diajukan untuk review.');
        }

        $job->update(['status' => 'pending_review']);

        // Notify all admin users about the new submission
        $admins = User::where('user_type', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new JobSubmittedForReview($job->title, $mitra->nama_perusahaan, $job->id));
        }

        return back()->with('success', 'Lowongan berhasil diajukan untuk review.');
    }

    private function validationRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'job_category_id' => 'nullable|exists:job_categories,id',
            'experience_level' => 'required|in:fresh_graduate,junior,mid_level,senior,internship',
            'location_type' => 'required|in:onsite,remote,hybrid',
            'location_city' => 'nullable|string',
            'tipe_kerja' => 'required|in:full_time,part_time,magang,freelance',
            'salary_min' => 'nullable|integer|min:0',
            'salary_max' => 'nullable|integer|min:0',
            'deadline' => 'nullable|date|after:today',
            'is_salary_visible' => 'boolean',
            'status' => 'in:draft,pending_review,closed',
        ];
    }
}
