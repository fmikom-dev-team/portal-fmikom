<?php

namespace App\Modules\Pagi\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Pagi\PagiCv;
use App\Models\User;
use App\Modules\Pagi\Services\PagiCvService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PagiCvController extends Controller implements HasMiddleware
{
    protected PagiCvService $cvService;

    private const ERROR_ACCESS_DENIED = 'Akses ditolak: Fitur CV Builder hanya tersedia untuk Mahasiswa dan Alumni.';
    private const ERROR_UNAUTHORIZED = 'Unauthorized access.';
    private const ERROR_UNAUTHORIZED_ACTION = 'Unauthorized';

    public function __construct(PagiCvService $cvService)
    {
        $this->cvService = $cvService;
    }

    public static function middleware(): array
    {
        return [
            new Middleware(function ($request, $next) {
                $role = $request->attributes->get('resolved_role', session('active_role'));
                if (! in_array(strtolower($role), ['mahasiswa', 'alumni'])) {
                    if ($request->wantsJson()) {
                        return response()->json(['message' => self::ERROR_ACCESS_DENIED], 403);
                    }

                    return redirect()->route('module.pagi.dashboard')
                        ->with('error', self::ERROR_ACCESS_DENIED);
                }

                return $next($request);
            }, except: ['shareView']),
        ];
    }

    /**
     * Display a list of the user's CVs.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $cvs = PagiCv::query()->where('user_id', $user->id)
            ->orderBy('updated_at', 'desc')
            ->get();

        return Inertia::render('Modules/Pagi/User/Cv/CvDashboard', [
            'cvs' => $cvs,
        ]);
    }

    /**
     * Display the template gallery page.
     */
    public function templates(Request $request)
    {
        $user = Auth::user();
        $existingCvsCount = PagiCv::query()->where('user_id', $user->id)->count('*');
        if ($existingCvsCount >= 3) {
            return redirect()->route('module.pagi.cv.index')
                ->with('error', 'Batas maksimal pembuatan CV adalah 3.');
        }

        return Inertia::render('Modules/Pagi/User/Cv/components/TemplateGallery');
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

        $user = Auth::user();

        try {
            $cv = $this->cvService->createCv($user, $request->template_id, $request->title);
            return redirect()->route('module.pagi.cv.edit', ['cv' => $cv->id])
                ->with('success', 'CV berhasil dibuat!');
        } catch (\RuntimeException $e) {
            return redirect()->route('module.pagi.cv.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Get the logged-in user's profile data to sync.
     */
    public function getProfileData(Request $request)
    {
        $user = Auth::user();
        $profileData = $this->cvService->mapUserProfileToCvData($user);

        return response()->json($profileData);
    }

    /**
     * Open the CV builder page.
     */
    public function edit(Request $request, PagiCv $cv): Response
    {
        if ($cv->user_id !== Auth::id()) {
            abort(403, self::ERROR_UNAUTHORIZED);
        }

        return Inertia::render('Modules/Pagi/User/Cv/CvBuilder', [
            'cv' => $cv,
        ]);
    }

    /**
     * Update CV data.
     */
    public function update(Request $request, PagiCv $cv)
    {
        if ($cv->user_id !== Auth::id()) {
            return response()->json(['error' => self::ERROR_UNAUTHORIZED_ACTION], 403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'in:draft,published'],
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

        $user = Auth::user();
        $this->cvService->updateCv($cv, $user, $data);

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
        if ($cv->user_id !== Auth::id()) {
            abort(403, self::ERROR_UNAUTHORIZED);
        }

        try {
            $this->cvService->duplicateCv($cv, Auth::id());
            return redirect()->route('module.pagi.cv.index')
                ->with('success', 'CV berhasil diduplikasi!');
        } catch (\RuntimeException $e) {
            return redirect()->route('module.pagi.cv.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a CV record.
     */
    public function destroy(Request $request, PagiCv $cv): RedirectResponse
    {
        if ($cv->user_id !== Auth::id()) {
            abort(403, self::ERROR_UNAUTHORIZED);
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
        if ($cv->user_id !== Auth::id()) {
            return response()->json(['error' => self::ERROR_UNAUTHORIZED_ACTION], 403);
        }

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);

        try {
            $path = $this->cvService->uploadPhoto($cv, $request->file('photo'));
            return response()->json([
                'success' => true,
                'path' => $path,
                'url' => asset('storage/'.$path),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Compile and download print-ready A4 PDF.
     */
    public function downloadPdf(Request $request, PagiCv $cv)
    {
        if ($cv->user_id !== Auth::id()) {
            abort(403, self::ERROR_UNAUTHORIZED);
        }

        $pdf = $this->generatePdf($cv);
        $filename = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $cv->title), '-')).'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Publicly view/stream print-ready A4 PDF.
     */
    public function shareView(PagiCv $cv)
    {
        $user = Auth::user();

        // Allow if owner, otherwise only allow published CVs
        if ($cv->user_id !== $user?->id && $cv->status !== 'published') {
            abort(403, 'CV ini bersifat privat atau belum dipublikasikan.');
        }

        $pdf = $this->generatePdf($cv);
        $filename = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $cv->title), '-')).'.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Helper to load view and generate PDF.
     */
    private function generatePdf(PagiCv $cv)
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

        return $pdf;
    }
}
