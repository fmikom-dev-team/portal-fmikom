<?php

namespace App\Console\Commands;

use App\Models\Surat;
use App\Models\SuratLampiran;
use App\Models\TemplateGlobalSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class AuditFastPrivateStorage extends Command
{
    protected $signature = 'fast:audit-private-storage';

    protected $description = 'Audit FAST storage paths to verify private-storage readiness before deploy';

    public function handle(): int
    {
        $this->info('Audit private storage FAST dimulai...');

        $issues = 0;

        $issues += $this->auditLampirans();
        $issues += $this->auditGeneratedDocuments();
        $issues += $this->auditLogoSetting();

        $this->newLine();

        if ($issues === 0) {
            $this->info('FAST private storage audit passed. Tidak ditemukan masalah.');

            return self::SUCCESS;
        }

        $this->error("FAST private storage audit menemukan {$issues} masalah.");

        return self::FAILURE;
    }

    private function auditLampirans(): int
    {
        $issues = 0;

        $this->comment('Memeriksa lampiran FAST...');

        SuratLampiran::query()
            ->select(['id', 'file_path'])
            ->whereNotNull('file_path')
            ->orderBy('id')
            ->chunkById(200, function ($lampirans) use (&$issues): void {
                foreach ($lampirans as $lampiran) {
                    $issues += $this->auditStoredPath(
                        sprintf('Lampiran #%d', $lampiran->id),
                        (string) $lampiran->file_path,
                    );
                }
            }, 'id');

        return $issues;
    }

    private function auditGeneratedDocuments(): int
    {
        $issues = 0;

        $this->comment('Memeriksa dokumen generated FAST...');

        Surat::query()
            ->select(['id', 'generated_file_path'])
            ->whereNotNull('generated_file_path')
            ->orderBy('id')
            ->chunkById(200, function ($surats) use (&$issues): void {
                foreach ($surats as $surat) {
                    $issues += $this->auditStoredPath(
                        sprintf('Surat #%d', $surat->id),
                        (string) $surat->generated_file_path,
                    );
                }
            }, 'id');

        return $issues;
    }

    private function auditLogoSetting(): int
    {
        $issues = 0;

        $this->comment('Memeriksa logo FAST...');

        $path = trim((string) TemplateGlobalSetting::get('logo_path', ''));

        if ($path === '') {
            $this->line('  - Logo FAST belum diset.');

            return 0;
        }

        return $this->auditStoredPath('TemplateGlobalSetting.logo_path', $path);
    }

    private function auditStoredPath(string $label, string $path): int
    {
        [$relativePath, $preferredDisk] = $this->normalizePath($path);

        if ($relativePath === '') {
            $this->warn("  - {$label}: path kosong.");

            return 1;
        }

        $publicExists = (bool) Storage::disk('public')->exists($relativePath);
        $localExists = (bool) Storage::disk('local')->exists($relativePath);

        if (! $publicExists && ! $localExists) {
            $this->warn("  - {$label}: file tidak ditemukan di local/private maupun public legacy ({$path}).");

            return 1;
        }

        if ($preferredDisk === 'public') {
            $this->warn("  - {$label}: path masih legacy public ({$path}).");

            return 1;
        }

        if ($publicExists) {
            $this->warn("  - {$label}: file masih ada di public legacy ({$relativePath}).");

            return 1;
        }

        $this->line("  - {$label}: OK ({$relativePath})");

        return 0;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function normalizePath(string $path): array
    {
        $path = trim($path);

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
