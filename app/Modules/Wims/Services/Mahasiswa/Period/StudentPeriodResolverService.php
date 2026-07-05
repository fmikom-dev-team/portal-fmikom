<?php

namespace App\Modules\Wims\Services\Mahasiswa\Period;

use App\Models\Magang\PendaftaranMagang;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class StudentPeriodResolverService
{
    private const SESSION_KEY = 'wims.selected_pendaftaran_id';

    public function resolveRegistrations(int $userId, array $with = ['perusahaan']): Collection
    {
        return PendaftaranMagang::query()
            ->with($with)
            ->forMahasiswa($userId)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->get();
    }

    public function resolveSelectedRegistration(int $userId, ?int $selectedRegistrationId = null, array $with = ['perusahaan']): ?PendaftaranMagang
    {
        $registrations = $this->resolveRegistrations($userId, $with);

        return $this->resolveSelectedRegistrationFromCollection($registrations, $selectedRegistrationId);
    }

    public function resolveSelectedRegistrationFromCollection(Collection $registrations, ?int $selectedRegistrationId = null): ?PendaftaranMagang
    {
        if (! $selectedRegistrationId || $selectedRegistrationId <= 0) {
            $selectedRegistrationId = $this->resolveSelectedRegistrationIdFromRequest();
        }

        if ($selectedRegistrationId) {
            $selected = $registrations->firstWhere('id', $selectedRegistrationId);

            if ($selected) {
                return $selected;
            }
        }

        return $registrations->firstWhere('status', 'aktif') ?? $registrations->first();
    }

    public function resolveSelectedRegistrationIdFromRequest(?Request $request = null): ?int
    {
        $request ??= request();

        if (! $request instanceof Request) {
            return null;
        }

        $queryValue = $request->query('pendaftaran');
        if ($queryValue !== null && $queryValue !== '') {
            $selectedId = (int) $queryValue;

            if ($selectedId > 0 && $request->hasSession()) {
                $request->session()->put(self::SESSION_KEY, $selectedId);
            }

            return $selectedId > 0 ? $selectedId : null;
        }

        if ($request->hasSession()) {
            $storedValue = $request->session()->get(self::SESSION_KEY);

            return is_numeric($storedValue) && (int) $storedValue > 0
                ? (int) $storedValue
                : null;
        }

        return null;
    }

    public function buildPeriodOptions(Collection $registrations, ?int $selectedRegistrationId = null): array
    {
        return $registrations
            ->map(function (PendaftaranMagang $registration) use ($selectedRegistrationId): array {
                return [
                    'id' => $registration->id,
                    'label' => $registration->periodLabel() ?? 'Periode belum ditentukan',
                    'company' => $registration->perusahaan?->nama ?? $registration->perusahaan_diminati_nama,
                    'status' => $registration->status,
                    'status_label' => $registration->statusLabel(),
                    'dashboard_phase' => $registration->dashboardPhase(),
                    'is_selected' => $selectedRegistrationId !== null && (int) $registration->id === (int) $selectedRegistrationId,
                    'is_active' => $registration->status === 'aktif',
                ];
            })
            ->values()
            ->all();
    }

    public function selectedRegistrationId(?PendaftaranMagang $registration): ?int
    {
        return $registration?->id;
    }
}