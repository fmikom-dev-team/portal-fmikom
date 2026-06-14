<?php

namespace App\Modules\Trace\Controllers\Alumni;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use App\Models\Tracer\CareerHistory;
use App\Models\Tracer\Provinsi;
use App\Models\Tracer\Kota;
use App\Services\CareerService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


/**
 * CareerController
 * 
 * Handles career history management for alumni including viewing,
 * creating, updating, and deleting career records with proper authorization.
 * 
 * @package App\Http\Controllers\AlumniTrace
 */
class CareerController extends Controller
{
    use AuthorizesRequests;
    protected CareerService $careerService;
    
    public function __construct(CareerService $careerService)
    {
        $this->careerService = $careerService;
    }

    /**
     * Display a listing of the user's career history
     * 
     * @return Response
     */
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', CareerHistory::class);
        $roleName = $request->attributes->get('resolved_role', session('active_role'));

        $user = auth()->user();
        $profile = $user->alumniProfile;
        
        if (!$profile) {
            return redirect()->route('profile-alumni')
                ->with('error', 'Silakan lengkapi profil Anda terlebih dahulu.');
        }
        
        // Get all careers ordered by is_current and created_at
        $careers = $profile->careers()
            ->orderByDesc('is_current')
            ->orderByDesc('created_at')
            ->with(['provinsi', 'kota', 'employment', 'education'])
            ->get();
        
        $flatCareers = collect($this->careerService->flattenCareers($careers));

        // Current career
        $currentCareer = $flatCareers->where('is_current', true)->first();
        
        // Career history (not current, only bekerja and wirausaha)
        $careerHistory = $flatCareers->where('is_current', false)
            ->whereIn('status', ['bekerja', 'wirausaha'])
            ->values()
            ->toArray();
            
        // Education history (not current, only lanjut_studi)
        $educationHistory = $flatCareers->where('is_current', false)
            ->where('status', 'lanjut_studi')
            ->values()
            ->toArray();
        
        // Statistics
        $stats = [
            'totalCareers' => $flatCareers->where('status', '!=', 'mencari_kerja')->count(),
            'totalCompanies' => $flatCareers->pluck('nama_perusahaan')->filter()->unique()->count(),
            'currentStatus' => $currentCareer['status'] ?? 'mencari_kerja',
            'yearsOfExperience' => $this->careerService->calculateYearsOfExperience($careers),
        ];
        
        Log::info('Career history viewed', [
            'user_id' => $user->id,
            'profile_id' => $profile->id,
        ]);
        
        return Inertia::render('Modules/Trace/Alumni/Career', [
            'roleName' => $roleName,
            'currentCareer' => $currentCareer,
            'careerHistory' => $careerHistory,
            'educationHistory' => $educationHistory,
            'stats' => $stats,
            'provinces' => Provinsi::all(),
            'cities' => Kota::all(),
        ]);
    }

    /**
     * Store a newly created career record
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', CareerHistory::class);

        $user = auth()->user();
        $profile = $user->alumniProfile;
        
        if (!$profile) {
            return back()->with('error', 'Profil tidak ditemukan.');
        }
        
        try {
            $validated = $request->validate([
                'status' => 'required|in:bekerja,wirausaha,mencari_kerja,lanjut_studi',
                // Work/Business fields
                'nama_perusahaan' => 'nullable|required_if:status,bekerja,wirausaha|string|max:255',
                'jabatan' => 'nullable|required_if:status,bekerja,wirausaha|string|max:255',
                'sektor_industri' => 'nullable|string|max:255',
                'alamat_perusahaan' => 'nullable|string',
                'gaji_min' => 'nullable|numeric|min:0',
                'gaji_max' => 'nullable|numeric|min:0|gte:gaji_min',
                // University fields
                'nama_universitas' => 'nullable|required_if:status,lanjut_studi|string|max:255',
                'program_studi_lanjutan' => 'nullable|required_if:status,lanjut_studi|string|max:255',
                'jenjang_pendidikan' => 'nullable|required_if:status,lanjut_studi|string|max:50',
                'sumber_biaya' => 'nullable|string|max:255',
                'alamat_universitas' => 'nullable|string',
                // Location fields
                'provinsi_id' => 'nullable|exists:provinsi,id',
                'kota_id' => 'nullable|exists:kota,id',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                // Date fields
                'tanggal_mulai' => 'nullable|date',
                'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
                'is_current' => 'nullable|boolean',
            ]);
            
            $this->careerService->store($profile, $validated);
            
            Log::info('Career record created', [
                'user_id' => $user->id,
                'profile_id' => $profile->id,
                'status' => $validated['status'],
            ]);
            
            return back()->with('success', 'Riwayat karir berhasil ditambahkan.');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
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

    /**
     * Update the specified career record
     * 
     * @param Request $request
     * @param string $id
     * @return RedirectResponse
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $user = auth()->user();
        $profile = $user->alumniProfile;
        
        if (!$profile) {
            return back()->with('error', 'Profil tidak ditemukan.');
        }
        
        try {
            $career = CareerHistory::where('profil_alumni_id', $profile->id)->findOrFail($id);
            
            $this->authorize('update', $career);
            
            $validated = $request->validate([
                'status' => 'required|in:bekerja,wirausaha,mencari_kerja,lanjut_studi',
                // Work/Business fields
                'nama_perusahaan' => 'nullable|required_if:status,bekerja,wirausaha|string|max:255',
                'jabatan' => 'nullable|required_if:status,bekerja,wirausaha|string|max:255',
                'sektor_industri' => 'nullable|string|max:255',
                'alamat_perusahaan' => 'nullable|string',
                'gaji_min' => 'nullable|numeric|min:0',
                'gaji_max' => 'nullable|numeric|min:0|gte:gaji_min',
                // University fields
                'nama_universitas' => 'nullable|required_if:status,lanjut_studi|string|max:255',
                'program_studi_lanjutan' => 'nullable|required_if:status,lanjut_studi|string|max:255',
                'jenjang_pendidikan' => 'nullable|required_if:status,lanjut_studi|string|max:50',
                'sumber_biaya' => 'nullable|string|max:255',
                'alamat_universitas' => 'nullable|string',
                // Location fields
                'provinsi_id' => 'nullable|exists:provinsi,id',
                'kota_id' => 'nullable|exists:kota,id',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                // Date fields
                'tanggal_mulai' => 'nullable|date',
                'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
                'is_current' => 'nullable|boolean',
            ]);
            
            $this->careerService->update($career, $validated);
            
            Log::info('Career record updated', [
                'user_id' => $user->id,
                'career_id' => $career->id,
                'status' => $validated['status'],
            ]);
            
            return back()->with('success', 'Riwayat karir berhasil diperbarui.');
            
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized career update attempt', [
                'user_id' => $user->id,
                'career_id' => $id,
            ]);
            return back()->with('error', 'Anda tidak memiliki izin untuk mengubah riwayat karir ini.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Career update validation failed', [
                'user_id' => $user->id,
                'career_id' => $id,
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

    /**
     * Remove the specified career record
     * 
     * @param string $id
     * @return RedirectResponse
     */
    public function destroy(string $id): RedirectResponse
    {
        $user = auth()->user();
        $profile = $user->alumniProfile;
        
        if (!$profile) {
            return back()->with('error', 'Profil tidak ditemukan.');
        }
        
        try {
            $career = CareerHistory::where('profil_alumni_id', $profile->id)->findOrFail($id);
            
            $this->authorize('delete', $career);
            
            // Don't allow deleting if it's the only career
            if ($profile->careers()->count() <= 1) {
                Log::warning('Attempted to delete only career record', [
                    'user_id' => $user->id,
                    'career_id' => $id,
                ]);
                return back()->with('error', 'Tidak dapat menghapus riwayat karir terakhir.');
            }
            
            $wasCurrent = $career->is_current;
            $career->delete();
            
            // If deleted career was current, set the latest one as current
            if ($wasCurrent) {
                $latestCareer = $profile->careers()->latest()->first();
                if ($latestCareer) {
                    $latestCareer->update(['is_current' => true]);
                }
            }
            
            Log::info('Career record deleted', [
                'user_id' => $user->id,
                'career_id' => $id,
                'was_current' => $wasCurrent,
            ]);
            
            return back()->with('success', 'Riwayat karir berhasil dihapus.');
            
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
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
    
    /**
     * Set a career as the current one
     * 
     * @param string $id
     * @return RedirectResponse
     */
    public function setCurrent(string $id): RedirectResponse
    {
        $user = auth()->user();
        $profile = $user->alumniProfile;
        
        if (!$profile) {
            return back()->with('error', 'Profil tidak ditemukan.');
        }
        
        try {
            $career = CareerHistory::where('profil_alumni_id', $profile->id)->findOrFail($id);
            
            $this->authorize('update', $career);
            
            $this->careerService->setCurrent($career);
            
            Log::info('Career set as current', [
                'user_id' => $user->id,
                'career_id' => $id,
            ]);
            
            return back()->with('success', 'Status karir saat ini berhasil diperbarui.');
            
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized career update attempt', [
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
}
