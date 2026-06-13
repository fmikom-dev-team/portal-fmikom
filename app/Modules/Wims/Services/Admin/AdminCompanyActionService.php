<?php

namespace App\Modules\Wims\Services\Admin;

use App\Models\PerusahaanMitra;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminCompanyActionService
{
    public function validateCompany(Request $request): array
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['nullable', 'string'],
            'kota' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_valid_meter' => ['nullable', 'numeric', 'min:10', 'max:5000'],
            'jam_masuk' => ['nullable', 'date_format:H:i'],
            'jam_pulang' => ['nullable', 'date_format:H:i'],
            'toleransi_terlambat_menit' => ['nullable', 'integer', 'min:0', 'max:240'],
            'hari_kerja' => ['required', 'array', 'min:1'],
            'hari_kerja.*' => ['required', 'string', Rule::in(PerusahaanMitra::workingDayOptions())],
            'bidang_industri' => ['nullable', 'string', 'max:255'],
            'kontak_person' => ['nullable', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ], [
            'latitude.between' => 'Latitude lokasi perusahaan tidak valid.',
            'longitude.between' => 'Longitude lokasi perusahaan tidak valid.',
            'radius_valid_meter.min' => 'Radius absensi minimal 10 meter.',
            'jam_masuk.date_format' => 'Format jam masuk harus HH:mm.',
            'jam_pulang.date_format' => 'Format jam pulang harus HH:mm.',
            'hari_kerja.required' => 'Hari kerja perusahaan wajib dipilih.',
        ]);

        if (($validated['latitude'] ?? null) !== null xor ($validated['longitude'] ?? null) !== null) {
            throw ValidationException::withMessages([
                'location' => 'Latitude dan longitude harus diisi bersamaan melalui map picker.',
            ]);
        }

        if (($validated['jam_masuk'] ?? null) !== null xor ($validated['jam_pulang'] ?? null) !== null) {
            throw ValidationException::withMessages([
                'schedule' => 'Jam masuk dan jam pulang harus diisi bersamaan.',
            ]);
        }

        return [
            ...$validated,
            'toleransi_terlambat_menit' => $validated['toleransi_terlambat_menit'] ?? 0,
            'hari_kerja' => collect($validated['hari_kerja'] ?? [])
                ->map(fn ($value) => strtolower((string) $value))
                ->unique()
                ->values()
                ->all(),
        ];
    }

    public function createCompany(array $validated): void
    {
        PerusahaanMitra::create($validated);
    }

    public function updateCompany(PerusahaanMitra $company, array $validated): void
    {
        $company->update($validated);
    }

    public function deleteCompany(PerusahaanMitra $company): void
    {
        DB::transaction(function () use ($company): void {
            $company->loadMissing('user');

            $mitraUser = $company->user;

            $company->delete();

            if ($mitraUser) {
                $mitraUser->delete();
            }
        });
    }

    public function validateAccount(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'jabatan' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ], [
            'password.confirmed' => 'Konfirmasi password akun mitra tidak cocok.',
            'email.unique' => 'Email akun mitra sudah digunakan oleh akun lain.',
        ]);
    }

    public function createCompanyAccount(PerusahaanMitra $company, array $validated): bool
    {
        $mitraRole = Role::query()->where('slug', 'mitra')->first();

        if (! $mitraRole) {
            return false;
        }

        DB::transaction(function () use ($company, $validated, $mitraRole): void {
            $user = new User();
            $user->forceFill([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role_id' => $mitraRole->id,
                'role_title' => $mitraRole->nama,
                'user_type' => 'mitra',
                'no_telepon' => $validated['no_telepon'] ?? null,
                'is_active' => (bool) $validated['is_active'],
                'email_verified_at' => now(),
            ])->save();

            $company->update([
                'user_id' => $user->id,
                'mitra_jabatan' => $validated['jabatan'] ?? null,
            ]);
        });

        return true;
    }
}
