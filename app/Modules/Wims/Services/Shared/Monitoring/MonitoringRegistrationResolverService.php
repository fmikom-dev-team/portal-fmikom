<?php

namespace App\Modules\Wims\Services\Shared\Monitoring;

use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class MonitoringRegistrationResolverService
{
    public function normalizeDateInput(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    public function resolveSelectedDate(
        mixed $queryDate,
        ?string $periodStart,
        ?string $periodEnd,
        string $defaultDate,
    ): string {
        $normalizedQueryDate = $this->normalizeDateInput($queryDate);

        if ($normalizedQueryDate) {
            return $this->clampDateToPeriod($normalizedQueryDate, $periodStart, $periodEnd);
        }

        $normalizedDefaultDate = $this->normalizeDateInput($defaultDate) ?? now()->toDateString();

        return $this->clampDateToPeriod($normalizedDefaultDate, $periodStart, $periodEnd);
    }

    public function resolveMonitoringReferenceDate(PendaftaranMagang $pendaftaran): string
    {
        if (blank($pendaftaran->tanggal_selesai)) {
            return now()->toDateString();
        }

        $today = now()->startOfDay();
        $endDate = Carbon::parse($pendaftaran->tanggal_selesai)->startOfDay();

        return $endDate->lessThan($today)
            ? $endDate->toDateString()
            : $today->toDateString();
    }

    public function resolveForLecturer(
        User $currentUser,
        int $mahasiswaId,
        string $date,
        ?int $pendaftaranId = null,
    ): ?PendaftaranMagang {
        $baseQuery = $this->authorizedLecturerQuery($currentUser, $mahasiswaId);

        if ($pendaftaranId) {
            return (clone $baseQuery)
                ->whereKey($pendaftaranId)
                ->first();
        }

        return $this->resolveByDate($baseQuery, $date);
    }

    public function resolveForCompany(
        PerusahaanMitra $company,
        int $mahasiswaId,
        string $date,
    ): ?PendaftaranMagang {
        $baseQuery = $this->authorizedCompanyQuery($company, $mahasiswaId);

        return $this->resolveByDate($baseQuery, $date);
    }

    private function clampDateToPeriod(string $date, ?string $periodStart, ?string $periodEnd): string
    {
        $carbonDate = Carbon::parse($date)->startOfDay();

        if ($periodStart) {
            $start = Carbon::parse($periodStart)->startOfDay();

            if ($carbonDate->lt($start)) {
                return $start->toDateString();
            }
        }

        if ($periodEnd) {
            $end = Carbon::parse($periodEnd)->startOfDay();

            if ($carbonDate->gt($end)) {
                return $end->toDateString();
            }
        }

        return $carbonDate->toDateString();
    }

    private function resolveByDate(Builder $baseQuery, string $date): ?PendaftaranMagang
    {
        $activePendaftaran = (clone $baseQuery)
            ->whereDate('tanggal_mulai', '<=', $date)
            ->whereDate('tanggal_selesai', '>=', $date)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->first();

        if ($activePendaftaran) {
            return $activePendaftaran;
        }

        $pendaftaranWithActivity = (clone $baseQuery)
            ->where(function (Builder $query) use ($date) {
                $query
                    ->whereHas('absensis', fn ($absensiQuery) => $absensiQuery->whereDate('tanggal', $date))
                    ->orWhereHas('logbooks', fn ($logbookQuery) => $logbookQuery->whereDate('tanggal', $date));
            })
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->first();

        if ($pendaftaranWithActivity) {
            return $pendaftaranWithActivity;
        }

        return (clone $baseQuery)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->first();
    }

    private function authorizedLecturerQuery(User $currentUser, int $mahasiswaId): Builder
    {
        return PendaftaranMagang::query()
            ->with(['perusahaan', 'mahasiswa'])
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('dosen_pembimbing_id', $currentUser->id);
    }

    private function authorizedCompanyQuery(PerusahaanMitra $company, int $mahasiswaId): Builder
    {
        return PendaftaranMagang::query()
            ->with(['perusahaan', 'mahasiswa'])
            ->where('mahasiswa_id', $mahasiswaId)
            ->where('perusahaan_id', $company->id);
    }
}
