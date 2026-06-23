<?php

namespace App\Modules\Trace\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trace\UpdateAlumniProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Tracer\ProfilAlumni;
use App\Models\Tracer\Provinsi;
use App\Models\Tracer\Kota;
use App\Models\ProgramStudi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Models\Tracer\ActivityLog;

class TraceAlumniProfileController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $user = Auth::user();
        $roleName = $request->attributes->get('resolved_role', session('active_role'));
        
        $profil = ProfilAlumni::with(['careers', 'educationHistories'])
            ->firstOrCreate(
                ['user_id' => $user->id],
                ['angkatan' => $user->tahun_lulus ?? null]
            );
            
        $alumniData = [
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
            
            'bio'                     => $user->bio,
            'location'                => $user->location,
            'website'                 => $user->website,
            'linkedin'                => $user->linkedin,
            'github'                  => $user->github,
            'instagram'               => $user->instagram,
            'twitter'                 => $user->twitter,

            'profil_id'               => $profil->id,
            'angkatan'                => $profil->angkatan,
            'alamat_rumah'            => $profil->alamat_rumah,
            'latitude_rumah'          => $profil->latitude_rumah,
            'longitude_rumah'         => $profil->longitude_rumah,
            'jenis_kelamin'           => $profil->jenis_kelamin,
            'nik'                     => $profil->nik ? str_repeat('*', 12) . substr($profil->nik, -4) : null,
            'npwp'                    => $profil->npwp ? str_repeat('*', max(0, strlen($profil->npwp) - 4)) . substr($profil->npwp, -4) : null,
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
            'provinsis' => Cache::remember('trace_provinsi_all', 3600, fn () => Provinsi::orderBy('name')->get(['id', 'name'])),
            'kotas' => Cache::remember('trace_kota_all', 3600, fn () => Kota::orderBy('name')->get(['id', 'name', 'provinsi_id'])),
            'programStudis' => Cache::remember('trace_prodi_all', 3600, fn () => ProgramStudi::orderBy('nama')->get(['id', 'nama', 'kode']))
        ]);
    }

    public function update(UpdateAlumniProfileRequest $request): RedirectResponse
    {
        $user = Auth::user();
        
        $profil = ProfilAlumni::firstOrCreate(
            ['user_id' => $user->id],
            ['angkatan' => $user->tahun_lulus ?? null]
        );

        $validated = $request->validated();

        DB::transaction(function () use ($user, $profil, $validated) {
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
        });

        ActivityLog::record('profile.updated', 'Memperbarui profil alumni');

        return redirect()->back()->with('success', 'Profil karir berhasil diperbarui');
    }
}