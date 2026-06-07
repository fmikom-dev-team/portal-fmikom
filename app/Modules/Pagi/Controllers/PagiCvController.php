<?php

namespace App\Modules\Pagi\Controllers;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Pagi\PagiCv;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class PagiCvController extends Controller
{
    /**
     * Display a list of the user's CVs.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        $cvs = PagiCv::where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return Inertia::render('Modules/Pagi/User/Cv/CvDashboard', [
            'cvs' => $cvs
        ]);
    }

    /**
     * Display the template gallery page.
     */
    public function templates(Request $request)
    {
        $user = auth()->user();
        $existingCvsCount = PagiCv::where('user_id', $user->id)->count();
        if ($existingCvsCount >= 3) {
            return redirect()->route('module.pagi.cv.index')
                ->with('error', 'Batas maksimal pembuatan CV adalah 3.');
        }

        return Inertia::render('Modules/Pagi/User/Cv/TemplateGallery');
    }

    /**
     * Create a new CV draft and redirect to the editor.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'template_id' => ['required', 'string', 'in:ats-professional,modern-sidebar,executive,creative-minimal,student-resume,custom'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $user = auth()->user();

        // Limit maximum CVs to 3
        $existingCvsCount = PagiCv::where('user_id', $user->id)->count();
        if ($existingCvsCount >= 3) {
            return redirect()->route('module.pagi.cv.index')
                ->with('error', 'Batas maksimal pembuatan CV adalah 3.');
        }

        // Gather initial personal info from profile
        $personalInfo = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->metadata['phone'] ?? '',
            'location' => $user->location ?? 'Banyumas, Indonesia',
            'website' => $user->website ?? '',
            'linkedin' => $user->linkedin ?? '',
            'github' => $user->github ?? '',
            'instagram' => $user->instagram ?? '',
            'job_title' => $user->role_title ?? '',
            'summary' => $user->bio ?? '',
            'foto_path' => $user->foto_path ?? '',
        ];

        // Gather initial certificates from profile metadata
        $certificates = [];
        $profileCertificates = $user->metadata['certificates'] ?? [];
        if (is_array($profileCertificates)) {
            foreach ($profileCertificates as $index => $cert) {
                $certificates[] = [
                    'id' => $index + 1,
                    'name' => $cert['title'] ?? '',
                    'issuer' => $cert['issuer'] ?? '',
                    'date' => $cert['date'] ?? '',
                    'credential_id' => $cert['credentialId'] ?? '',
                    'credential_url' => '',
                ];
            }
        }

        // Initialize empty lists
        $education = [];
        $experience = [];
        $organizations = [];
        $skills = [];
        
        // Let's populate some default skills from profile if available
        $profileSkills = $user->metadata['skills'] ?? [];
        if (is_array($profileSkills)) {
            foreach ($profileSkills as $index => $skillItem) {
                // skillItem can be a string or an array like {name, percentage}
                $skillName = is_array($skillItem) ? ($skillItem['name'] ?? '') : (string) $skillItem;
                if (empty(trim($skillName))) continue;
                $skills[] = [
                    'id'    => $index + 1,
                    'name'  => $skillName,
                    'level' => is_array($skillItem) ? ($skillItem['percentage'] ?? 80) : 80,
                ];
            }
        }

        $trainings = [];
        $achievements = [];
        $languages = [];
        
        $profileLanguages = $user->metadata['languages'] ?? [];
        if (is_array($profileLanguages)) {
            foreach ($profileLanguages as $index => $langItem) {
                // langItem can be a string or an array like {language, proficiency}
                $langName = is_array($langItem) ? ($langItem['language'] ?? $langItem['name'] ?? '') : (string) $langItem;
                $langProf = is_array($langItem) ? ($langItem['proficiency'] ?? 'Professional working proficiency') : 'Professional working proficiency';
                if (empty(trim($langName))) continue;
                $languages[] = [
                    'id'          => $index + 1,
                    'name'        => $langName,
                    'proficiency' => $langProf,
                ];
            }
        }

        $references = [];

        // Setup theme/customization default based on template
        $customization = [
            'primary_color' => $this->getDefaultColorForTemplate($request->template_id),
            'font_family' => 'GT Standard M',
            'font_size' => '11pt',
            'line_height' => '1.4',
            'spacing' => 'normal', // compact, normal, loose
            'section_order' => [
                'summary',
                'experience',
                'education',
                'organizations',
                'skills',
                'certifications',
                'trainings',
                'achievements',
                'languages',
                'references'
            ],
            'sections_visibility' => [
                'summary' => true,
                'experience' => true,
                'education' => true,
                'organizations' => true,
                'skills' => true,
                'certifications' => true,
                'trainings' => true,
                'achievements' => true,
                'languages' => true,
                'references' => true,
            ]
        ];

        $title = $request->title ?: 'CV ' . ucfirst(str_replace('-', ' ', $request->template_id));

        $cv = PagiCv::create([
            'user_id' => $user->id,
            'title' => $title,
            'template_id' => $request->template_id,
            'personal_info' => $personalInfo,
            'education' => $education,
            'experience' => $experience,
            'organizations' => $organizations,
            'skills' => $skills,
            'certifications' => $certificates,
            'trainings' => $trainings,
            'achievements' => $achievements,
            'languages' => $languages,
            'references' => $references,
            'customization' => $customization,
            'status' => 'draft',
        ]);

        return redirect()->route('module.pagi.cv.edit', ['cv' => $cv->id])
            ->with('success', 'CV berhasil dibuat!');
    }

    /**
     * Get the logged-in user's profile data to sync.
     */
    public function getProfileData(Request $request)
    {
        $user = auth()->user();

        $personalInfo = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->metadata['phone'] ?? '',
            'location' => $user->location ?? 'Banyumas, Indonesia',
            'website' => $user->website ?? '',
            'linkedin' => $user->linkedin ?? '',
            'github' => $user->github ?? '',
            'instagram' => $user->instagram ?? '',
            'job_title' => $user->role_title ?? '',
            'summary' => $user->bio ?? '',
            'foto_path' => $user->foto_path ?? '',
        ];

        $certificates = [];
        $profileCertificates = $user->metadata['certificates'] ?? [];
        if (is_array($profileCertificates)) {
            foreach ($profileCertificates as $index => $cert) {
                $certificates[] = [
                    'id' => $index + 1,
                    'name' => $cert['title'] ?? '',
                    'issuer' => $cert['issuer'] ?? '',
                    'date' => $cert['date'] ?? '',
                    'credential_id' => $cert['credentialId'] ?? '',
                    'credential_url' => '',
                ];
            }
        }

        $skills = [];
        $profileSkills = $user->metadata['skills'] ?? [];
        if (is_array($profileSkills)) {
            foreach ($profileSkills as $index => $skillItem) {
                $skillName = is_array($skillItem) ? ($skillItem['name'] ?? '') : (string) $skillItem;
                if (empty(trim($skillName))) continue;
                $skills[] = [
                    'id'    => $index + 1,
                    'name'  => $skillName,
                    'level' => is_array($skillItem) ? ($skillItem['percentage'] ?? 80) : 80,
                ];
            }
        }

        $languages = [];
        $profileLanguages = $user->metadata['languages'] ?? [];
        if (is_array($profileLanguages)) {
            foreach ($profileLanguages as $index => $langItem) {
                $langName = is_array($langItem) ? ($langItem['language'] ?? $langItem['name'] ?? '') : (string) $langItem;
                $langProf = is_array($langItem) ? ($langItem['proficiency'] ?? 'Professional working proficiency') : 'Professional working proficiency';
                if (empty(trim($langName))) continue;
                $languages[] = [
                    'id'          => $index + 1,
                    'name'        => $langName,
                    'proficiency' => $langProf,
                ];
            }
        }

        return response()->json([
            'personal_info' => $personalInfo,
            'skills' => $skills,
            'certifications' => $certificates,
            'languages' => $languages,
        ]);
    }

    /**
     * Open the CV builder page.
     */
    public function edit(Request $request, PagiCv $cv): Response
    {
        // Check owner
        if ($cv->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        return Inertia::render('Modules/Pagi/User/Cv/CvBuilder', [
            'cv' => $cv
        ]);
    }

    /**
     * Update the CV details (autosave & save).
     */
    public function update(Request $request, PagiCv $cv)
    {
        if ($cv->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:draft,published'], // will be overridden server-side
            'personal_info' => ['nullable', 'array'],
            'education' => ['nullable', 'array'],
            'experience' => ['nullable', 'array'],
            'organizations' => ['nullable', 'array'],
            'skills' => ['nullable', 'array'],
            'certifications' => ['nullable', 'array'],
            'trainings' => ['nullable', 'array'],
            'achievements' => ['nullable', 'array'],
            'languages' => ['nullable', 'array'],
            'references' => ['nullable', 'array'],
            'customization' => ['nullable', 'array'],
        ]);

        $data = $this->sanitizeInputRecursive($data);

        // Auto-compute status based on completeness
        $pi = $data['personal_info'] ?? [];
        $hasBasic = !empty(trim($pi['name'] ?? ''))
            && !empty(trim($pi['email'] ?? ''))
            && !empty(trim($pi['phone'] ?? ''))
            && !empty(trim($pi['summary'] ?? ''));
        $hasSection = !empty($data['education']) || !empty($data['experience']);
        $data['status'] = ($hasBasic && $hasSection) ? 'published' : 'draft';

        $cv->update($data);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'CV berhasil disimpan!',
                'updated_at' => $cv->updated_at->toIso8601String(),
            ]);
        }

        return redirect()->back()->with('success', 'CV berhasil disimpan!');
    }

    /**
     * Duplicate an existing CV.
     */
    public function duplicate(Request $request, PagiCv $cv): RedirectResponse
    {
        if ($cv->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        // Limit maximum CVs to 3
        $existingCvsCount = PagiCv::where('user_id', auth()->id())->count();
        if ($existingCvsCount >= 3) {
            return redirect()->route('module.pagi.cv.index')
                ->with('error', 'Batas maksimal pembuatan CV adalah 3.');
        }

        $newCv = $cv->replicate();
        $newCv->title = 'Salinan dari ' . $cv->title;
        $newCv->save();

        return redirect()->route('module.pagi.cv.index')
            ->with('success', 'CV berhasil diduplikasi!');
    }

    /**
     * Delete a CV record.
     */
    public function destroy(Request $request, PagiCv $cv): RedirectResponse
    {
        if ($cv->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        $cv->delete();

        return redirect()->route('module.pagi.cv.index')
            ->with('success', 'CV berhasil dihapus!');
    }

    /**
     * Upload a custom profile photo for the CV.
     */
    public function uploadPhoto(Request $request, PagiCv $cv)
    {
        if ($cv->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        if ($request->file('photo')) {
            $path = $request->file('photo')->store('cv-photos', 'public');
            
            // Update the stored cv record's personal_info with the new path
            $personalInfo = $cv->personal_info ?? [];
            
            // Delete old photo if it exists and is specific to cv
            if (!empty($personalInfo['foto_path']) && str_contains($personalInfo['foto_path'], 'cv-photos/')) {
                Storage::disk('public')->delete($personalInfo['foto_path']);
            }
            
            $personalInfo['foto_path'] = $path;
            $cv->update(['personal_info' => $personalInfo]);

            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => asset('storage/' . $path),
            ]);
        }

        return response()->json(['error' => 'File not found'], 400);
    }

    /**
     * Compile and download print-ready A4 PDF.
     */
    public function downloadPdf(Request $request, PagiCv $cv)
    {
        if ($cv->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access.');
        }

        $pdf = Pdf::loadView('pdf.cv', [
            'cv' => $cv,
            'personalInfo' => $cv->personal_info ?? [],
            'education' => $cv->education ?? [],
            'experience' => $cv->experience ?? [],
            'organizations' => $cv->organizations ?? [],
            'skills' => $cv->skills ?? [],
            'certifications' => $cv->certifications ?? [],
            'trainings' => $cv->trainings ?? [],
            'achievements' => $cv->achievements ?? [],
            'languages' => $cv->languages ?? [],
            'references' => $cv->references ?? [],
            'customization' => $cv->customization ?? [],
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setWarnings(false);

        $filename = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $cv->title), '-')) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Publicly view/stream print-ready A4 PDF.
     */
    public function shareView(PagiCv $cv)
    {
        $pdf = Pdf::loadView('pdf.cv', [
            'cv' => $cv,
            'personalInfo' => $cv->personal_info ?? [],
            'education' => $cv->education ?? [],
            'experience' => $cv->experience ?? [],
            'organizations' => $cv->organizations ?? [],
            'skills' => $cv->skills ?? [],
            'certifications' => $cv->certifications ?? [],
            'trainings' => $cv->trainings ?? [],
            'achievements' => $cv->achievements ?? [],
            'languages' => $cv->languages ?? [],
            'references' => $cv->references ?? [],
            'customization' => $cv->customization ?? [],
        ]);

        $pdf->setPaper('a4', 'portrait');
        $pdf->setWarnings(false);

        $filename = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $cv->title), '-')) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Helper to get default template accent colors.
     */
    private function getDefaultColorForTemplate(string $templateId): string
    {
        switch ($templateId) {
            case 'ats-professional':
                return '#1e3a8a'; // Blue 900
            case 'modern-sidebar':
                return '#0ea5e9'; // Cyan 500
            case 'executive':
                return '#111827'; // Dark Slate
            case 'creative-minimal':
                return '#6b21a8'; // Purple 800
            case 'student-resume':
                return '#10b981'; // Emerald 500
            default:
                return '#1e293b';
        }
    }

    /**
     * Recursively sanitize input to prevent HTML/Script tag injection.
     */
    private function sanitizeInputRecursive($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->sanitizeInputRecursive($val);
            }
            return $value;
        }

        if (is_string($value)) {
            return strip_tags($value);
        }

        return $value;
    }
}
