<?php

namespace App\Modules\Trace\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tracer\ProfilAlumni;
use App\Models\User;
use App\Modules\Trace\Services\AlumniRoleChangeService;
use App\Modules\Trace\Services\CareerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RespondenController extends Controller
{
    public function __construct(
        protected CareerService $careerService,
        protected AlumniRoleChangeService $roleChangeService,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $query = ProfilAlumni::with(['user.programStudi', 'careers.employment']);

        if ($request->filled('search')) {
            $search = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $request->search);
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nomor_induk', 'like', "%{$search}%");
            });
        }

        if ($request->filled('prodi')) {
            $prodi = $request->prodi;
            $query->whereHas('user.programStudi', function ($q) use ($prodi) {
                $q->where('nama', $prodi);
            });
        }

        if ($request->filled('status')) {
            $status = $request->status;
            $query->whereHas('careers', function ($q) use ($status) {
                $q->where('is_current', true)->where('status', $status);
            });
        }

        if ($request->filled('angkatan')) {
            $query->where('angkatan', $request->angkatan);
        }

        $totalAlumni = Cache::remember('trace_total_alumni', now()->addMinutes(10), function () {
            return ProfilAlumni::count();
        });
        $alumniList = $query->paginate(15)->withQueryString();

        return Inertia::render('Modules/Trace/Admin/Alumni', [
            'alumniList' => $alumniList,
            'totalAlumni' => $totalAlumni,
            'filters' => $request->only(['search', 'status', 'prodi', 'angkatan']),
            'pendingRoleChangeRequests' => $this->pendingRoleChangeRequests(),
        ]);
    }

    public function show($id): InertiaResponse
    {
        $alumni = ProfilAlumni::with([
            'user.programStudi',
            'provinsi',
            'kota',
            'careers' => fn ($q) => $q->orderByDesc('is_current')->orderByDesc('tanggal_mulai')->with(['provinsi', 'kota', 'employment', 'education']),
            'educationHistories',
        ])->findOrFail($id);

        $flatCareers = collect($this->careerService->flattenCareers($alumni->careers));
        $currentCareer = $flatCareers->where('is_current', true)->first();
        $careerHistory = $flatCareers
            ->whereIn('status', ['bekerja', 'wirausaha'])
            ->values()
            ->toArray();
        $educationHistory = $flatCareers
            ->where('status', 'lanjut_studi')
            ->values()
            ->toArray();

        $alumniPayload = array_merge(
            $alumni->append(['nik_masked', 'npwp_masked'])->toArray(),
            [
                'nama_lengkap' => $alumni->nama_lengkap,
                'nim' => $alumni->nim,
                'program_studi' => $alumni->program_studi,
                'tahun_lulus' => $alumni->tahun_lulus,
                'careers' => $flatCareers->values()->toArray(),
            ],
        );

        return Inertia::render('Modules/Trace/Admin/AlumniDetail', [
            'alumni' => $alumniPayload,
            'currentCareer' => $currentCareer,
            'currentEducation' => $currentCareer && $currentCareer['status'] === 'lanjut_studi'
                ? $currentCareer
                : null,
            'careerHistory' => $careerHistory,
            'educationHistory' => $educationHistory,
        ]);
    }

    public function approveRoleChange(Request $request, int $userId): RedirectResponse
    {
        $user = User::query()->findOrFail($userId);

        $this->roleChangeService->approve($user, $request->user());

        return back()->with('status', 'Pengajuan perubahan role berhasil disetujui.');
    }

    public function rejectRoleChange(Request $request, int $userId): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $user = User::query()->findOrFail($userId);

        $this->roleChangeService->reject($user, $request->user(), $validated['reason'] ?? null);

        return back()->with('status', 'Pengajuan perubahan role berhasil ditolak.');
    }

    public function showRoleChangeProof(int $userId): BinaryFileResponse
    {
        $user = User::query()->findOrFail($userId);
        $roleChangeRequest = $this->roleChangeService->requestFrom($user);
        $data = $roleChangeRequest['data'] ?? [];
        $proofPath = $data['proof_path'] ?? null;

        abort_unless(($roleChangeRequest['status'] ?? null) === 'pending', 404);
        abort_unless(is_string($proofPath) && str_starts_with($proofPath, 'trace/alumni-role-change-proofs/'), 404);

        $disk = Storage::disk('local')->exists($proofPath) ? 'local' : 'public';

        abort_unless(Storage::disk($disk)->exists($proofPath), 404);

        $fileName = str_replace(['"', '\\'], '', $data['proof_original_name'] ?? 'bukti-kelulusan');

        return response()->file(Storage::disk($disk)->path($proofPath), [
            'Content-Type' => $data['proof_mime'] ?? 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.$fileName.'"',
            'Content-Security-Policy' => "sandbox; default-src 'none'; script-src 'none'; object-src 'none'; img-src 'self' data: blob:;",
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'no-referrer',
        ]);
    }

    private function pendingRoleChangeRequests(): array
    {
        return User::query()
            ->with('programStudi')
            ->where('user_type', 'mahasiswa')
            ->whereNotNull('metadata')
            ->get()
            ->filter(fn (User $user) => ($this->roleChangeService->requestFrom($user)['status'] ?? null) === 'pending')
            ->map(function (User $user) {
                $request = $this->roleChangeService->requestFrom($user);
                $data = $request['data'] ?? [];

                return [
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'nomor_induk' => $user->nomor_induk,
                    'program_studi' => $user->programStudi?->nama,
                    'submitted_at' => $request['submitted_at'] ?? null,
                    'data' => $data,
                    'proof' => [
                        'path' => $data['proof_path'] ?? null,
                        'url' => ! empty($data['proof_path'])
                            ? route('module.trace.admin.alumni.role-change.proof', $user->id)
                            : null,
                        'original_name' => $data['proof_original_name'] ?? null,
                        'mime' => $data['proof_mime'] ?? null,
                        'size' => $data['proof_size'] ?? null,
                    ],
                ];
            })
            ->values()
            ->toArray();
    }
}
