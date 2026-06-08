<?php

namespace App\Modules\Trace\Controllers\Alumni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tracer\ProfilAlumni;
use App\Models\Tracer\Provinsi;
use App\Models\Tracer\Kota;
use App\Models\ProgramStudi;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TraceAlumniProfileController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $roleName = $request->attributes->get('resolved_role', session('active_role'));
        
        $profil = ProfilAlumni::with(['careers', 'educationHistories'])
            ->firstOrCreate(
                ['user_id' => $user->id],
                ['angkatan' => $user->tahun_lulus ?? null]
            );
            
        $alumniData = [
            // Identitas Utama & Akun (Tabel Users)
            'user_id'                 => $user->id,
            'name'                    => $user->name,
            'email'                   => $user->email,
            'nomor_induk'             => $user->nomor_induk,
            'no_telepon'              => $user->no_telepon,
            'foto_path'               => $user->foto_path,
            'banner_path'             => $user->banner_path,
            'tahun_lulus'             => $user->tahun_lulus,
            'tanggal_lahir'           => $user->tanggal_lahir,
            'program_studi_id'        => $user->program_studi_id,
            
            // Profil Sosial Media & Karakter (Tabel Users)
            'bio'                     => $user->bio,
            'location'                => $user->location,
            'website'                 => $user->website,
            'linkedin'                => $user->linkedin,
            'github'                  => $user->github,
            'instagram'               => $user->instagram,
            'twitter'                 => $user->twitter,

            // Data Tracer / Profil Tambahan (Tabel ProfilAlumni)
            'profil_id'               => $profil->id,
            'angkatan'                => $profil->angkatan,
            'alamat_rumah'            => $profil->alamat_rumah,
            'latitude_rumah'          => $profil->latitude_rumah,
            'longitude_rumah'         => $profil->longitude_rumah,
            'jenis_kelamin'           => $profil->jenis_kelamin,
            'nik'                     => $profil->nik,
            'npwp'                    => $profil->npwp,
            'provinsi_id'             => $profil->provinsi_id,
            'kota_id'                 => $profil->kota_id,
            'completeness_percentage' => $profil->completeness_percentage,
            
            // Riwayat Pendidikan & Karir
            'careers'                 => $profil->careers,
            'education_histories'     => $profil->educationHistories,
        ];

        return Inertia::render('Modules/Trace/Alumni/ProfileAlumni', [
            'roleName'   => $roleName,
            'alumni' => $alumniData,
            'provinsis' => Provinsi::orderBy('name')->get(['id', 'name']),
            'kotas' => Kota::orderBy('name')->get(['id', 'name', 'provinsi_id']),
            'programStudis' => ProgramStudi::orderBy('nama')->get(['id', 'nama', 'kode'])
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $profil = ProfilAlumni::firstOrCreate(
            ['user_id' => $user->id],
            ['angkatan' => $user->tahun_lulus ?? null]
        );

        $validated = $request->validate([
            // Users table validation
            'name' => ['required', 'string', 'max:255'],
            'nomor_induk' => ['required', 'string', 'max:50'],
            'tahun_lulus' => ['nullable', 'integer', 'min:1900', 'max:' . (date('Y') + 5)],
            'no_telepon' => ['nullable', 'string', 'max:25'],
            'program_studi_id' => ['nullable', 'exists:program_studis,id'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'github' => ['nullable', 'string', 'max:255'],
            'instagram' => ['nullable', 'string', 'max:255'],
            'twitter' => ['nullable', 'string', 'max:255'],
            'linkedin' => ['nullable', 'string', 'max:255'],

            // ProfilAlumni table validation
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'angkatan' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 5)],
            'nik' => ['nullable', 'string', 'size:16'],
            'npwp' => ['nullable', 'string', 'max:30'],
            'provinsi_id' => ['nullable', 'exists:provinsi,id'],
            'kota_id' => ['nullable', 'exists:kota,id'],
            'alamat_rumah' => ['nullable', 'string'],
            'latitude_rumah' => ['nullable', 'numeric'],
            'longitude_rumah' => ['nullable', 'numeric'],
        ]);

        // Update User
        $user->update([
            'name' => $validated['name'],
            'nomor_induk' => $validated['nomor_induk'],
            'tahun_lulus' => $validated['tahun_lulus'],
            'no_telepon' => $validated['no_telepon'],
            'program_studi_id' => $validated['program_studi_id'],
            'bio' => $validated['bio'] ?? null,
            'location' => $validated['location'] ?? null,
            'website' => $validated['website'] ?? null,
            'github' => $validated['github'] ?? null,
            'instagram' => $validated['instagram'] ?? null,
            'twitter' => $validated['twitter'] ?? null,
            'linkedin' => $validated['linkedin'] ?? null,
        ]);

        // Update ProfilAlumni
        $profil->update([
            'jenis_kelamin' => $validated['jenis_kelamin'] ?? null,
            'angkatan' => $validated['angkatan'],
            'nik' => $validated['nik'] ?? null,
            'npwp' => $validated['npwp'] ?? null,
            'provinsi_id' => $validated['provinsi_id'] ?? null,
            'kota_id' => $validated['kota_id'] ?? null,
            'alamat_rumah' => $validated['alamat_rumah'] ?? null,
            'latitude_rumah' => $validated['latitude_rumah'] ?? null,
            'longitude_rumah' => $validated['longitude_rumah'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Profil karir berhasil diperbarui');
    }
}