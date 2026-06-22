<?php

namespace App\Console\Commands;

use App\Models\Surat;
use App\Models\SuratLampiran;
use App\Models\TemplateGlobalSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MigrateFastFilesToPrivate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fast:migrate-files-to-private
        {--dry-run : Tampilkan rencana migrasi tanpa menyalin atau menghapus file}
        {--keep-source : Jangan hapus file public setelah berhasil disalin}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pindahkan file FAST lama dari public storage ke private storage secara aman';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $keepSource = (bool) $this->option('keep-source');

        $this->info('Memulai migrasi file FAST ke private storage...');

        $migrated = 0;
        $skipped = 0;
        $failed = 0;

        $migrated += $this->migrateSuratLampirans($dryRun, $keepSource, $skipped, $failed);
        $migrated += $this->migrateGeneratedPdf($dryRun, $keepSource, $skipped, $failed);
        $migrated += $this->migrateLogoSetting($dryRun, $keepSource, $skipped, $failed);

        $this->newLine();
        $this->info('Migrasi file FAST selesai.');
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

    private function migrateSuratLampirans(bool $dryRun, bool $keepSource, int &$skipped, int &$failed): int
    {
        $this->comment('Mengecek lampiran surat FAST...');

        $migrated = 0;

        SuratLampiran::query()
            ->select(['id', 'file_path', 'nama_file'])
            ->whereNotNull('file_path')
            ->orderBy('id')
            ->chunkById(100, function ($lampirans) use ($dryRun, $keepSource, &$migrated, &$skipped, &$failed): void {
                foreach ($lampirans as $lampiran) {
                    $migrated += $this->migratePath(
                        label: sprintf('Lampiran #%d', $lampiran->id),
                        path: (string) $lampiran->file_path,
                        sourceDisk: null,
                        dryRun: $dryRun,
                        keepSource: $keepSource,
                        skipped: $skipped,
                        failed: $failed,
                    );
                }
            }, 'id');

        return $migrated;
    }

    private function migrateGeneratedPdf(bool $dryRun, bool $keepSource, int &$skipped, int &$failed): int
    {
        $this->comment('Mengecek PDF final FAST...');

        $migrated = 0;

        Surat::query()
            ->select(['id', 'generated_file_path', 'nomor_surat', 'status'])
            ->whereNotNull('generated_file_path')
            ->orderBy('id')
            ->chunkById(100, function ($surats) use ($dryRun, $keepSource, &$migrated, &$skipped, &$failed): void {
                foreach ($surats as $surat) {
                    $migrated += $this->migratePath(
                        label: sprintf('Surat #%d', $surat->id),
                        path: (string) $surat->generated_file_path,
                        sourceDisk: null,
                        dryRun: $dryRun,
                        keepSource: $keepSource,
                        skipped: $skipped,
                        failed: $failed,
                    );
                }
            }, 'id');

        return $migrated;
    }

    private function migrateLogoSetting(bool $dryRun, bool $keepSource, int &$skipped, int &$failed): int
    {
        $this->comment('Mengecek logo kop FAST...');

        $logoPathRaw = (string) TemplateGlobalSetting::get('logo_path', '');
        $logoPath = trim($logoPathRaw);

        if ($logoPath === '') {
            $this->line('  - Logo kop belum diset.');

            return 0;
        }

        $normalized = $this->normalizePath($logoPath);

        if ($normalized === null) {
            $this->line("  - Lewati logo kop: format path tidak dikenali ({$logoPath})");
            $skipped++;

            return 0;
        }

        [$relativePath, $sourceDisk] = $normalized;

        $publicExists = Storage::disk('public')->exists($relativePath);
        $privateExists = Storage::disk('local')->exists($relativePath);

        if (! $publicExists && ! $privateExists) {
            $this->line("  - Logo kop tidak ditemukan: {$logoPath}");
            $skipped++;

            return 0;
        }

        if ($privateExists && ! $publicExists) {
            $this->line("  - Logo kop sudah private: {$relativePath}");

            if ($logoPath !== $relativePath && ! $dryRun) {
                TemplateGlobalSetting::set('logo_path', $relativePath);
            }

            $skipped++;

            return 0;
        }

        if ($dryRun) {
            $this->line("  - Dry-run logo kop: {$logoPath} -> {$relativePath}");
            if ($logoPath !== $relativePath) {
                $this->line("    * Setting akan dinormalisasi ke: {$relativePath}");
            }

            return 1;
        }

        try {
            if ($publicExists && ! $privateExists) {
                $contents = Storage::disk('public')->get($relativePath);
                Storage::disk('local')->put($relativePath, $contents);
            }

            if ($publicExists && ! $keepSource) {
                Storage::disk('public')->delete($relativePath);
            }

            if ($logoPath !== $relativePath) {
                TemplateGlobalSetting::set('logo_path', $relativePath);
            }

            $this->info("  - Logo kop dipindah: {$relativePath}");

            return 1;
        } catch (Throwable $exception) {
            $failed++;
            $this->error("  - Gagal logo kop: {$logoPath} :: {$exception->getMessage()}");

            return 0;
        }
    }

    private function migratePath(
        string $label,
        string $path,
        ?string $sourceDisk,
        bool $dryRun,
        bool $keepSource,
        int &$skipped,
        int &$failed,
    ): int {
        $normalized = $this->normalizePath($path);

        if ($normalized === null) {
            $this->line("  - Lewati {$label}: path kosong");
            $skipped++;

            return 0;
        }

        [$relativePath, $preferredSource] = $normalized;
        $sourceDisk = $sourceDisk ?? $preferredSource;

        $source = Storage::disk($sourceDisk);
        $target = Storage::disk('local');
        $legacy = Storage::disk('public');

        $sourceExists = $source->exists($relativePath);
        $legacyExists = $legacy->exists($relativePath);
        $privateExists = $target->exists($relativePath);

        if (! $sourceExists && ! $legacyExists && ! $privateExists) {
            $this->line("  - Tidak ditemukan: {$label} => {$path}");
            $skipped++;

            return 0;
        }

        if ($privateExists && ! $legacyExists && $sourceDisk === 'local') {
            $this->line("  - Sudah private: {$label} => {$relativePath}");
            $skipped++;

            return 0;
        }

        if ($dryRun) {
            $this->line("  - Dry-run: {$label} => {$path} -> {$relativePath}");
            return 1;
        }

        try {
            if (! $privateExists) {
                $contents = $sourceExists
                    ? $source->get($relativePath)
                    : $legacy->get($relativePath);

                if ($contents === false || $contents === '') {
                    throw new \RuntimeException('Gagal membaca isi file.');
                }

                $target->put($relativePath, $contents);
            }

            if (! $target->exists($relativePath)) {
                throw new \RuntimeException('File tidak terdeteksi setelah disalin.');
            }

            if (! $keepSource) {
                if ($sourceExists && $sourceDisk === 'public') {
                    $source->delete($relativePath);
                }

                if ($legacyExists) {
                    $legacy->delete($relativePath);
                }
            }

            $this->info("  - Pindah: {$label} => {$relativePath}");

            return 1;
        } catch (Throwable $exception) {
            $failed++;
            $this->error("  - Gagal: {$label} => {$path} :: {$exception->getMessage()}");

            return 0;
        }
    }

    /**
     * @return array{0: string, 1: string}|null
     */
    private function normalizePath(string $path): ?array
    {
        $path = trim($path);

        if ($path === '') {
            return null;
        }

        if (str_starts_with($path, '/storage/')) {
            return [ltrim(substr($path, strlen('/storage/')), '/'), 'public'];
        }

        if (str_starts_with($path, 'storage/')) {
            return [ltrim(substr($path, strlen('storage/')), '/'), 'public'];
        }

        if (str_starts_with($path, '/private/')) {
            return [ltrim(substr($path, strlen('/private/')), '/'), 'local'];
        }

        if (str_starts_with($path, 'private/')) {
            return [ltrim(substr($path, strlen('private/')), '/'), 'local'];
        }

        return [ltrim($path, '/'), 'local'];
    }
}
