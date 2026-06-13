<?php

namespace App\Console\Commands;

use App\Services\MonitoringAlertService;
use Illuminate\Console\Command;

class CheckMonitoringAlerts extends Command
{
    protected $signature = 'wims:check-monitoring-alerts {--threshold=3 : Ambang jumlah hari kosong sebelum warning aktif}';

    protected $description = 'Memeriksa mahasiswa aktif yang tidak melakukan presensi atau logbook lebih dari ambang hari monitoring.';

    public function handle(MonitoringAlertService $monitoringAlertService): int
    {
        $threshold = max((int) $this->option('threshold'), 0);
        $warnings = $monitoringAlertService->getWarningsForActiveInternships($threshold);

        if ($warnings->isEmpty()) {
            $this->info('Tidak ada warning monitoring aktif saat ini.');

            return self::SUCCESS;
        }

        $this->info(sprintf('Ditemukan %d warning monitoring aktif.', $warnings->count()));

        $this->table(
            ['Mahasiswa', 'NIM', 'Perusahaan', 'Absensi Kosong', 'Logbook Kosong'],
            $warnings->map(fn (array $warning) => [
                $warning['name'] ?? '-',
                $warning['nim'] ?? '-',
                $warning['company'] ?? '-',
                $warning['attendance_missing_days'],
                $warning['logbook_missing_days'],
            ])->all(),
        );

        return self::SUCCESS;
    }
}
