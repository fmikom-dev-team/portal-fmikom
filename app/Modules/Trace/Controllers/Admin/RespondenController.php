<?php

namespace App\Modules\Trace\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tracer\ProfilAlumni;
use Inertia\Inertia;

class RespondenController extends Controller
{
    public function index(Request $request)
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

        $totalAlumni = ProfilAlumni::count();
        $alumniList = $query->paginate(15)->withQueryString();

        return Inertia::render('Modules/Trace/Admin/Alumni', [
            'alumniList' => $alumniList,
            'totalAlumni' => $totalAlumni,
            'filters' => $request->only(['search', 'status', 'prodi', 'angkatan']),
        ]);
    }

    public function show($id)
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
            'alumni' => $alumni,
            'currentCareer' => $currentCareer,
            'currentEducation' => $currentEducation,
        ]);
    }
}
