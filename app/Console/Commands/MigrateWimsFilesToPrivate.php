<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class MigrateWimsFilesToPrivate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wims:migrate-files-to-private
        {--dry-run : Tampilkan rencana migrasi tanpa menyalin atau menghapus file}
        {--keep-source : Jangan hapus file public setelah berhasil disalin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pindahkan file WIMS lama dari public storage ke private storage secara aman';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $folders = [
            'profile-photos',
            'absensi/check-in',
            'absensi/check-out',
            'ketidakhadiran',
            'logbook',
            'laporan-akhir',
        ];

        $dryRun = (bool) $this->option('dry-run');
        $keepSource = (bool) $this->option('keep-source');

        $this->info('Memulai migrasi file WIMS ke private storage...');

        $migrated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($folders as $folder) {
            $this->line("Memeriksa folder: {$folder}");

            $files = Storage::disk('public')->allFiles($folder);

            if ($files === []) {
                $this->line("  - Tidak ada file public di {$folder}");

                continue;
            }

            foreach ($files as $path) {
                if (Storage::disk('local')->exists($path)) {
                    $skipped++;
                    $this->line("  - Lewati (sudah ada di private): {$path}");

                    continue;
                }

                if ($dryRun) {
                    $this->line("  - Dry-run: {$path}");

                    continue;
                }

                try {
                    $source = Storage::disk('public');
                    $target = Storage::disk('local');

                    $contents = $source->get($path);

                    if ($contents === false || $contents === '') {
                        throw new RuntimeException('Gagal membaca isi file.');
                    }

                    $target->put($path, $contents);

                    if (! $target->exists($path)) {
                        throw new RuntimeException('File tidak terdeteksi setelah disalin.');
                    }

                    if (! $keepSource) {
                        $source->delete($path);
                    }

                    $migrated++;
                    $this->info("  - Pindah: {$path}");
                } catch (Throwable $exception) {
                    $failed++;
                    $this->error("  - Gagal: {$path} :: {$exception->getMessage()}");
                }
            }
        }

        $this->newLine();
        $this->info('Migrasi file WIMS selesai.');
        $this->line("Berhasil dipindah: {$migrated}");
        $this->line("Dilewati: {$skipped}");
        $this->line("Gagal: {$failed}");

        if ($dryRun) {
            $this->warn('Mode dry-run aktif, tidak ada file yang diubah.');
        }

        if ($keepSource) {
            $this->warn('Opsi keep-source aktif, file public tidak dihapus.');
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
