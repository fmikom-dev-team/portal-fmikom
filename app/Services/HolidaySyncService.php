<?php

namespace App\Services;

use App\Models\HariLibur;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class HolidaySyncService
{
    public function sync(?array $years = null): Collection
    {
        $years ??= [
            now()->year,
            now()->addYear()->year,
        ];

        return collect($years)
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->values()
            ->map(fn (int $year) => [
                'year' => $year,
                'count' => $this->syncYear($year),
            ]);
    }

    public function syncYear(int $year): int
    {
        $response = Http::baseUrl((string) config('services.holiday_api.base_url'))
            ->acceptJson()
            ->timeout(20)
            ->retry(2, 500)
            ->get(sprintf('/api/v3/PublicHolidays/%d/%s', $year, config('services.holiday_api.country_code', 'ID')));

        if ($response->failed()) {
            throw new RuntimeException(sprintf(
                'Gagal mengambil data libur nasional tahun %d. HTTP %s',
                $year,
                $response->status()
            ));
        }

        $holidays = collect($response->json())
            ->filter(fn (array $holiday) => $this->shouldStoreHoliday($holiday));

        foreach ($holidays as $holiday) {
            HariLibur::query()->updateOrCreate(
                ['tanggal' => $holiday['date']],
                [
                    'nama' => $holiday['localName'] ?: $holiday['name'],
                    'is_active' => true,
                ],
            );
        }

        return $holidays->count();
    }

    private function shouldStoreHoliday(array $holiday): bool
    {
        $date = data_get($holiday, 'date');

        if (! $date || ! Carbon::hasFormat($date, 'Y-m-d')) {
            return false;
        }

        $types = collect(data_get($holiday, 'types', []));

        return (bool) data_get($holiday, 'global', false)
            && $types->contains('Public');
    }
}
