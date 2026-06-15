<?php

namespace App\Modules\Trace\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Tracer\MitraProfiles;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MitraProfileController extends Controller
{
    public function setup(Request $request)
    {
        $user = $request->user();

        // If profile already exists, redirect to edit
        if ($user->mitraProfile) {
            return redirect()->route('module.trace.open-job.mitra-profile');
        }

        return Inertia::render('Modules/Trace/Mitra/ProfileSetup');
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->mitraProfile) {
            return redirect()->route('module.trace.open-job.mitra-profile')
                ->with('error', 'Profil mitra sudah ada.');
        }

        $validated = $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'email_perusahaan' => 'nullable|email|max:255',
            'no_telp' => 'nullable|string|max:20',
            'alamat_lengkap' => 'nullable|string',
            'provinsi_id' => 'nullable|exists:provinsi,id',
            'kota_id' => 'nullable|exists:kota,id',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('mitra-logos', 'public');
            $validated['logo_path'] = $path;
        }

        unset($validated['logo']);
        $validated['user_id'] = $user->id;

        MitraProfiles::create($validated);

        return redirect()->route('module.trace.open-job.mitra-dashboard')
            ->with('success', 'Profil mitra berhasil dibuat.');
    }

    public function edit(Request $request)
    {
        $user = $request->user();
        $mitra = $user->mitraProfile;

        if (!$mitra) {
            return redirect()->route('module.trace.open-job.mitra-profile-setup');
        }

        return Inertia::render('Modules/Trace/Mitra/ProfileEdit', [
            'mitra' => $mitra,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $mitra = $user->mitraProfile;

        if (!$mitra) {
            return redirect()->route('module.trace.open-job.mitra-profile-setup');
        }

        $validated = $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'email_perusahaan' => 'nullable|email|max:255',
            'no_telp' => 'nullable|string|max:20',
            'alamat_lengkap' => 'nullable|string',
            'provinsi_id' => 'nullable|exists:provinsi,id',
            'kota_id' => 'nullable|exists:kota,id',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($mitra->logo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($mitra->logo_path);
            }
            $path = $request->file('logo')->store('mitra-logos', 'public');
            $validated['logo_path'] = $path;
        }

        unset($validated['logo']);
        $mitra->update($validated);

        return back()->with('success', 'Profil mitra berhasil diperbarui.');
    }
}
