<?php

namespace App\Modules\Trace\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tracer\ProfilAlumni;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class RespondenController extends Controller
{
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
        ]);
    }

    public function show($id): InertiaResponse
    {
        $alumni = ProfilAlumni::with([
            'user.programStudi',
            'provinsi',
            'kota',
            'careers' => fn($q) => $q->orderByDesc('is_current')->orderByDesc('tanggal_mulai')->with(['provinsi', 'kota', 'employment', 'education']),
            'educationHistories'
        ])->findOrFail($id);

        $currentCareer = $alumni->careers->where('is_current', true)->first();
        $currentEducation = $alumni->careers->where('status', 'lanjut_studi')->where('is_current', true)->first();

        return Inertia::render('Modules/Trace/Admin/AlumniDetail', [
            'alumni' => $alumni->append(['nik_masked', 'npwp_masked']),
            'currentCareer' => $currentCareer,
            'currentEducation' => $currentEducation,
        ]);
    }
}
