<?php

namespace App\Modules\Wims\Services\Admin;

use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use App\Modules\Wims\Services\Shared\Portal\WimsModuleRoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminCompanyActionService
{
    public function __construct(
        private readonly WimsModuleRoleService $wimsModuleRoleService,
    ) {}

    public function validateCompany(Request $request): array
    {
        $isCreate = ! $request->route('company');
        $required = fn (string $field): array => $isCreate ? ['required'] : ['nullable'];

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => [...$required('alamat'), 'string'],
            'kota' => [...$required('kota'), 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_valid_meter' => [...$required('radius_valid_meter'), 'numeric', 'min:10', 'max:5000'],
            'jam_masuk' => [...$required('jam_masuk'), 'date_format:H:i'],
            'jam_pulang' => [...$required('jam_pulang'), 'date_format:H:i'],
            'toleransi_terlambat_menit' => [...$required('toleransi_terlambat_menit'), 'integer', 'min:0', 'max:240'],
            'hari_kerja' => ['required', 'array', 'min:1'],
            'hari_kerja.*' => ['required', 'string', Rule::in(PerusahaanMitra::workingDayOptions())],
            'bidang_industri' => [...$required('bidang_industri'), 'string', 'max:255'],
            'kontak_person' => ['nullable', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ], [
            'nama.required' => 'Nama perusahaan wajib diisi.',
            'alamat.required' => 'Alamat perusahaan wajib diisi.',
            'kota.required' => 'Kota wajib diisi.',
            'radius_valid_meter.required' => 'Radius presensi wajib diisi.',
            'radius_valid_meter.min' => 'Radius absensi minimal 10 meter.',
            'jam_masuk.required' => 'Jam masuk wajib diisi.',
            'jam_masuk.date_format' => 'Format jam masuk harus HH:mm.',
            'jam_pulang.required' => 'Jam pulang wajib diisi.',
            'jam_pulang.date_format' => 'Format jam pulang harus HH:mm.',
            'toleransi_terlambat_menit.required' => 'Toleransi terlambat wajib diisi.',
            'toleransi_terlambat_menit.min' => 'Toleransi terlambat minimal 0 menit.',
            'toleransi_terlambat_menit.max' => 'Toleransi terlambat maksimal 240 menit.',
            'latitude.between' => 'Latitude lokasi perusahaan tidak valid.',
            'longitude.between' => 'Longitude lokasi perusahaan tidak valid.',
            'bidang_industri.required' => 'Bidang industri wajib diisi.',
            'email.email' => 'Format email perusahaan tidak valid.',
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
            $mitraUserId = $mitraUser?->id;

            $company->delete();

            if (! $mitraUserId) {
                return;
            }

            $hasOtherLinkedCompanies = PerusahaanMitra::query()
                ->where('user_id', $mitraUserId)
                ->exists();

            if (! $hasOtherLinkedCompanies) {
                $this->wimsModuleRoleService->ensureAssignment($mitraUser, 'mitra', false);
            }
        });
    }

    public function validateAccountLink(Request $request): array
    {
        return $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'jabatan' => ['nullable', 'string', 'max:255'],
        ]);
    }

    public function linkCompanyPortalAccount(PerusahaanMitra $company, array $validated): bool
    {
        if (! $this->wimsModuleRoleService->resolveModule() || ! $this->wimsModuleRoleService->resolveRole('mitra')) {
            return false;
        }

        $user = User::query()
            ->where('email', $validated['email'])
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => 'Akun Portal dengan email tersebut belum ditemukan.',
            ]);
        }

        $alreadyLinkedCompany = PerusahaanMitra::query()
            ->where('user_id', $user->id)
            ->whereKeyNot($company->id)
            ->first();

        if ($alreadyLinkedCompany) {
            throw ValidationException::withMessages([
                'email' => 'Akun Portal ini sudah terhubung ke perusahaan mitra lain.',
            ]);
        }

        DB::transaction(function () use ($company, $validated, $user): void {
            $this->wimsModuleRoleService->ensureAssignment($user, 'mitra', true);

            $company->update([
                'user_id' => $user->id,
                'mitra_jabatan' => $validated['jabatan'] ?? null,
            ]);
        });

        return true;
    }
}
