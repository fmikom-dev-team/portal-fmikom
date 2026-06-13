<?php

namespace App\Console\Commands;

use App\Services\HolidaySyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncNationalHolidays extends Command
{
    protected $signature = 'wims:sync-national-holidays {--year=* : Tahun yang ingin disinkronkan. Default: tahun ini dan tahun depan}';

    protected $description = 'Menyinkronkan tanggal merah dan libur nasional dari layanan holiday API ke tabel hari_liburs.';

    public function handle(HolidaySyncService $holidaySyncService): int
    {
        $years = collect((array) $this->option('year'))
            ->filter()
            ->map(fn ($year) => (int) $year)
            ->values()
            ->all();

        try {
            $results = $holidaySyncService->sync($years ?: null);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Sinkronisasi libur nasional selesai.');
        $this->table(
            ['Tahun', 'Jumlah Tersinkron'],
            $results->map(fn (array $result) => [
                $result['year'],
                $result['count'],
            ])->all(),
        );

        return self::SUCCESS;
    }
}
