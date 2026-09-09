<?php

namespace App\Modules\Wims\Services\Admin;

use App\Models\Magang\PerusahaanMitra;
use App\Models\Magang\PendaftaranMagang;
use App\Models\User;
use App\Modules\Wims\Services\Shared\Portal\WimsModuleRoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
        // Saat edit, field operasional boleh tidak dikirim agar partial update tetap aman,
        // tetapi jika dikirim tidak boleh dikosongkan atau diubah menjadi null.
        $required = fn (string $field): array => $isCreate ? ['required'] : ['sometimes', 'required'];

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
            'portal_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'mitra_jabatan' => ['nullable', 'string', 'max:255'],
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
            'portal_user_id.exists' => 'Akun Portal mitra yang dipilih tidak ditemukan.',
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
        DB::transaction(function () use ($validated): void {
            $company = PerusahaanMitra::create($validated);

            $this->syncPortalAccount($company, $validated);
        });
    }

    public function resolveGoogleMapsLink(string $url): array
    {
        $parsed = parse_url($url);
        $host = strtolower((string) ($parsed['host'] ?? ''));
        $scheme = strtolower((string) ($parsed['scheme'] ?? ''));

        if (! in_array($scheme, ['http', 'https'], true) || ! $this->isAllowedMapHost($host)) {
            throw ValidationException::withMessages([
                'url' => 'Tautan harus berasal dari Google Maps.',
            ]);
        }

        try {
            $response = Http::timeout(10)
                ->withOptions([
                    'allow_redirects' => [
                        'max' => 5,
                        'on_redirect' => function ($request, $response, $uri): void {
                            if (! $this->isAllowedMapHost(strtolower($uri->getHost()))) {
                                throw new \RuntimeException('Redirect host tidak diizinkan.');
                            }
                        },
                    ],
                ])
                ->get($url);
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'url' => 'Tautan Google Maps tidak dapat dibaca. Coba gunakan URL lengkap atau pilih titik di peta.',
            ]);
        }

        if (! $response->successful() || ! $this->isAllowedMapHost(strtolower((string) $response->effectiveUri()->getHost()))) {
            throw ValidationException::withMessages([
                'url' => 'Tautan Google Maps tidak dapat dibaca. Coba gunakan URL lengkap atau pilih titik di peta.',
            ]);
        }

        $coordinates = $this->extractCoordinates(implode("\n", [
            (string) $response->effectiveUri(),
            $response->body(),
        ]));

        if (! $coordinates) {
            throw ValidationException::withMessages([
                'url' => 'Koordinat tidak ditemukan dari tautan tersebut. Coba salin URL lokasi yang lebih lengkap.',
            ]);
        }

        return [
            'latitude' => $coordinates[0],
            'longitude' => $coordinates[1],
        ];
    }

    public function updateCompany(PerusahaanMitra $company, array $validated): void
    {
        DB::transaction(function () use ($company, $validated): void {
            $company->update($validated);

            $this->syncPortalAccount($company, $validated);
        });
    }

    public function deleteCompany(PerusahaanMitra $company): void
    {
        if ($this->hasPlacementHistory($company)) {
            throw ValidationException::withMessages([
                'company' => 'Perusahaan yang sudah dipakai dalam pendaftaran tidak dapat dihapus. Nonaktifkan perusahaan untuk mempertahankan riwayat PKL.',
            ]);
        }

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

    private function syncPortalAccount(PerusahaanMitra $company, array $validated): void
    {
        $portalUserId = $validated['portal_user_id'] ?? null;

        if (! $portalUserId) {
            return;
        }

        $user = User::query()->find($portalUserId);

        if (! $user) {
            throw ValidationException::withMessages([
                'portal_user_id' => 'Akun Portal mitra yang dipilih tidak ditemukan.',
            ]);
        }

        if (! $this->wimsModuleRoleService->hasActiveRole($user->id, 'mitra')) {
            throw ValidationException::withMessages([
                'portal_user_id' => 'Akun yang dipilih harus memiliki role mitra aktif.',
            ]);
        }

        $alreadyLinkedCompany = PerusahaanMitra::query()
            ->where('user_id', $user->id)
            ->whereKeyNot($company->id)
            ->first();

        if ($alreadyLinkedCompany) {
            throw ValidationException::withMessages([
                'portal_user_id' => 'Akun Portal ini sudah terhubung ke perusahaan mitra lain.',
            ]);
        }

        DB::transaction(function () use ($company, $validated, $user): void {
            $this->wimsModuleRoleService->ensureAssignment($user, 'mitra', true);

            $company->update([
                'user_id' => $user->id,
                'mitra_jabatan' => $validated['mitra_jabatan'] ?? $company->mitra_jabatan,
            ]);
        });
    }

    private function isAllowedMapHost(string $host): bool
    {
        return $host === 'maps.app.goo.gl'
            || $host === 'maps.google.com'
            || $host === 'google.com'
            || str_ends_with($host, '.google.com')
            || str_ends_with($host, '.google.co.id');
    }

    private function hasPlacementHistory(PerusahaanMitra $company): bool
    {
        return PendaftaranMagang::query()
            ->where('perusahaan_id', $company->id)
            ->exists();
    }

    /** @return array{0: float, 1: float}|null */
    private function extractCoordinates(string $value): ?array
    {
        $patterns = [
            '/@(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/',
            '/!3d(-?\d{1,3}\.\d+)!4d(-?\d{1,3}\.\d+)/',
            '/(?:[?&](?:q|query)=)(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value, $matches) !== 1) {
                continue;
            }

            $latitude = (float) $matches[1];
            $longitude = (float) $matches[2];

            if ($latitude >= -90 && $latitude <= 90 && $longitude >= -180 && $longitude <= 180) {
                return [$latitude, $longitude];
            }
        }

        return null;
    }
}
