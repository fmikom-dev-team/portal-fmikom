<?php

namespace App\Modules\Trace\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\Tracer\ActivityLog;
use App\Models\Tracer\CareerHistory;
use App\Models\Tracer\Kota;
use App\Models\Tracer\Provinsi;
use App\Modules\Trace\Services\CareerService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CareerController extends Controller
{
    use AuthorizesRequests;

    protected CareerService $careerService;

    public function __construct(CareerService $careerService)
    {
        $this->careerService = $careerService;
    }

    public function index(Request $request): Response|RedirectResponse
    {
        $this->authorize('viewAny', CareerHistory::class);
        $role = $request->attributes->get('resolved_role', session('active_role'));

        $user = auth()->user();
        $profile = $user->alumniProfile;

        if (! $profile) {
            return redirect()->route('profile-alumni')
                ->with('error', 'Silakan lengkapi profil Anda terlebih dahulu.');
        }

        $careers = $profile->careers()
            ->orderByDesc('is_current')
            ->orderByDesc('created_at')
            ->with(['provinsi', 'kota', 'employment', 'education'])
            ->get();

        $flatCareers = collect($this->careerService->flattenCareers($careers));

        $currentCareer = $flatCareers->where('is_current', true)->first();

        $careerHistory = $flatCareers->where('is_current', false)
            ->whereIn('status', ['bekerja', 'wirausaha'])
            ->values()
            ->toArray();

        $educationHistory = $flatCareers->where('is_current', false)
            ->where('status', 'lanjut_studi')
            ->values()
            ->toArray();

        $stats = [
            'totalCareers' => $flatCareers->where('status', '!=', 'mencari_kerja')->count(),
            'totalCompanies' => $flatCareers->pluck('nama_perusahaan')->filter()->unique()->count(),
            'currentStatus' => $currentCareer['status'] ?? 'mencari_kerja',
            'yearsOfExperience' => $this->careerService->calculateYearsOfExperience($careers),
        ];

        Log::debug('Career history viewed', [
            'user_id' => $user->id,
            'profile_id' => $profile->id,
        ]);

        return Inertia::render('Modules/Trace/Alumni/Career', [
            'currentCareer' => $currentCareer,
            'careerHistory' => $careerHistory,
            'educationHistory' => $educationHistory,
            'stats' => $stats,
            'provinces' => Cache::remember('trace_provinsi_all', 3600, fn () => Provinsi::select('id', 'name')->orderBy('name')->get()),
            'cities' => Cache::remember('trace_kota_all', 3600, fn () => Kota::select('id', 'name', 'provinsi_id')->orderBy('name')->get()),
            'roleName' => $role,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CareerHistory::class);

        $user = auth()->user();
        $profile = $user->alumniProfile;

        if (! $profile) {
            return back()->with('error', 'Profil tidak ditemukan.');
        }

        try {
            $validated = $request->validate($this->careerValidationRules());

            $this->careerService->store($profile, $validated);

            Log::debug('Career record created', [
                'user_id' => $user->id,
                'profile_id' => $profile->id,
                'status' => $validated['status'],
            ]);

            ActivityLog::record('career.created', 'Menambah riwayat karir: '.($validated['jabatan'] ?? $validated['status']).' di '.($validated['nama_perusahaan'] ?? '-'));

            return back()->with('success', 'Riwayat karir berhasil ditambahkan.');

        } catch (ValidationException $e) {
            Log::warning('Career store validation failed', [
                'user_id' => $user->id,
                'errors' => $e->errors(),
            ]);

            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Career store error', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menyimpan data karir.');
        }
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $user = auth()->user();
        $profile = $user->alumniProfile;

        if (! $profile) {
            return back()->with('error', 'Profil tidak ditemukan.');
        }

        try {
            $career = CareerHistory::where('profil_alumni_id', $profile->id)->findOrFail($id);

            $this->authorize('update', $career);

            $validated = $request->validate($this->careerValidationRules());

            $this->careerService->update($career, $validated);

            Log::debug('Career record updated', [
                'user_id' => $user->id,
                'career_id' => $career->id,
                'status' => $validated['status'],
            ]);

            ActivityLog::record('career.updated', "Memperbarui riwayat karir: {$career->jabatan}", $career);

            return back()->with('success', 'Riwayat karir berhasil diperbarui.');

        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized career update attempt', [
                'user_id' => $user->id,
                'career_id' => $id,
            ]);

            return back()->with('error', 'Anda tidak memiliki izin untuk mengubah riwayat karir ini.');
        } catch (ValidationException $e) {
            Log::warning('Career update validation failed', [
                'user_id' => $user->id,
                'career_id' => $id,
                'errors' => $e->errors(),
            ]);

            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Career update error', [
                'user_id' => $user->id,
                'career_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat memperbarui data karir.');
        }
    }

    public function destroy(string $id): RedirectResponse
    {
        $user = auth()->user();
        $profile = $user->alumniProfile;

        if (! $profile) {
            return back()->with('error', 'Profil tidak ditemukan.');
        }

        try {
            $career = CareerHistory::where('profil_alumni_id', $profile->id)->findOrFail($id);

            $this->authorize('delete', $career);

            if ($profile->careers->count() <= 1) {
                Log::warning('Attempted to delete only career record', [
                    'user_id' => $user->id,
                    'career_id' => $id,
                ]);

                return back()->with('error', 'Tidak dapat menghapus riwayat karir terakhir.');
            }

            $wasCurrent = $career->is_current;

            ActivityLog::record('career.deleted', "Menghapus riwayat karir: {$career->jabatan}", $career);

            $career->delete();

            if ($wasCurrent) {
                $latestCareer = $profile->careers()->latest()->first();
                if ($latestCareer) {
                    $latestCareer->update(['is_current' => true]);
                }
            }

            Log::debug('Career record deleted', [
                'user_id' => $user->id,
                'career_id' => $id,
                'was_current' => $wasCurrent,
            ]);

            return back()->with('success', 'Riwayat karir berhasil dihapus.');

        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized career delete attempt', [
                'user_id' => $user->id,
                'career_id' => $id,
            ]);

            return back()->with('error', 'Anda tidak memiliki izin untuk menghapus riwayat karir ini.');
        } catch (\Exception $e) {
            Log::error('Career delete error', [
                'user_id' => $user->id,
                'career_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat menghapus data karir.');
        }
    }

    public function setCurrent(string $id): RedirectResponse
    {
        $user = auth()->user();
        $profile = $user->alumniProfile;

        if (! $profile) {
            return back()->with('error', 'Profil tidak ditemukan.');
        }

        try {
            $career = CareerHistory::where('profil_alumni_id', $profile->id)->findOrFail($id);

            $this->authorize('update', $career);

            $this->careerService->setCurrent($career);

            Log::debug('Career set as current', [
                'user_id' => $user->id,
                'career_id' => $id,
            ]);

            return back()->with('success', 'Status karir saat ini berhasil diperbarui.');

        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized career setCurrent attempt', [
                'user_id' => $user->id,
                'career_id' => $id,
            ]);

            return back()->with('error', 'Anda tidak memiliki izin untuk mengubah status karir ini.');
        } catch (\Exception $e) {
            Log::error('Career setCurrent error', [
                'user_id' => $user->id,
                'career_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Terjadi kesalahan saat memperbarui status karir.');
        }
    }

    private function careerValidationRules(): array
    {
        return [
            'status' => 'required|in:bekerja,wirausaha,mencari_kerja,lanjut_studi',
            'nama_perusahaan' => 'nullable|required_if:status,bekerja,wirausaha|string|max:255',
            'jabatan' => 'nullable|required_if:status,bekerja,wirausaha|string|max:255',
            'sektor_industri' => 'nullable|string|max:255',
            'alamat_perusahaan' => 'nullable|string',
            'gaji_min' => 'nullable|numeric|min:0',
            'gaji_max' => 'nullable|numeric|min:0|gte:gaji_min',
            'nama_universitas' => 'nullable|required_if:status,lanjut_studi|string|max:255',
            'program_studi_lanjutan' => 'nullable|required_if:status,lanjut_studi|string|max:255',
            'jenjang_pendidikan' => 'nullable|required_if:status,lanjut_studi|string|max:50',
            'sumber_biaya' => 'nullable|string|max:255',
            'alamat_universitas' => 'nullable|string',
            'provinsi_id' => 'nullable|exists:provinsi,id',
            'kota_id' => 'nullable|exists:kota,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'is_current' => 'nullable|boolean',
        ];
    }
}
