<?php

namespace App\Modules\Wims\Services\Mahasiswa\Logbook;

use App\Models\Magang\LogbookMagang;
use App\Models\Magang\PendaftaranMagang;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use Throwable;

class LogbookExportService
{
    public function downloadCurrentPeriod(int $userId)
    {
        app()->setLocale('id');
        Carbon::setLocale('id');

        $registration = PendaftaranMagang::query()
            ->with([
                'mahasiswa.programStudi.fakultas',
                'perusahaan.user',
                'dosenPembimbing',
            ])
            ->forMahasiswa($userId)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->get()
            ->first(fn (PendaftaranMagang $item) => $item->status === 'aktif' || $item->isPostInternshipPhase());

        abort_if($registration === null, 404);

        $logbooks = LogbookMagang::query()
            ->where('pendaftaran_id', $registration->id)
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->orderBy('id')
            ->get();

        $student = $registration->mahasiswa;
        $programStudi = $student?->programStudi;
        $rows = $logbooks->map(function (LogbookMagang $logbook, int $index) {
            $activityContent = $this->buildStructuredContent($logbook->aktivitas_harian);
            $competencyContent = $this->buildStructuredContent($logbook->kompetensi_dicapai);

            return [
                'number' => $index + 1,
                'date' => $this->formatLocalizedDate($logbook->tanggal, 'd F Y'),
                'start_time' => $this->formatTimeValue($logbook->jam_mulai) ?? '-',
                'end_time' => $this->formatTimeValue($logbook->jam_selesai) ?? '-',
                'activity' => $activityContent['text'],
                'activity_items' => $activityContent['items'],
                'competency' => $competencyContent['text'],
                'competency_items' => $competencyContent['items'],
                'status' => $this->formatStatusLabel($logbook->status),
                'mentor_note' => $logbook->catatan_mitra ?? $logbook->catatan_dosen ?? '-',
            ];
        });

        return $this->renderPdfWithIsolatedCompiledViews('pdf.logbook-history', [
            'student' => [
                'name' => $student?->name ?? '-',
                'nim' => $student?->nim_nip ?: $student?->nomor_induk ?: '-',
                'program_studi' => $programStudi?->nama ?? '-',
            ],
            'internship' => [
                'company' => $registration->perusahaan?->nama ?? '-',
                'period' => $registration->tanggal_mulai && $registration->tanggal_selesai
                    ? $registration->tanggal_mulai->locale('id')->translatedFormat('d M Y').' - '.$registration->tanggal_selesai->locale('id')->translatedFormat('d M Y')
                    : '-',
                'supervisor_lecturer' => $registration->dosenPembimbing?->name ?? '-',
                'mentor' => $registration->perusahaan?->user?->name ?? '-',
            ],
            'rows' => $rows,
        ], 'logbook-pkl-periode-terakhir-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Render PDF using a request-scoped compiled view directory to avoid
     * Windows file-lock collisions in storage/framework/views.
     */
    private function renderPdfWithIsolatedCompiledViews(string $view, array $data, string $fileName)
    {
        $compiledPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR
            . 'wims-pdf-views-'
            . Str::uuid();

        File::ensureDirectoryExists($compiledPath);

        $blade = app('blade.compiler');
        $originalPath = $this->getBladeCompiledPath($blade);

        try {
            $this->setBladeCompiledPath($compiledPath);

            return Pdf::loadView($view, $data)
                ->setPaper('a4', 'landscape')
                ->download($fileName);
        } finally {
            if (is_string($originalPath) && $originalPath !== '') {
                $this->setBladeCompiledPath($originalPath);
            }
        }
    }

    private function setBladeCompiledPath(string $path): void
    {
        $blade = app('blade.compiler');

        if (! $blade instanceof BladeCompiler) {
            return;
        }

        $reflection = new \ReflectionObject($blade);

        if ($reflection->hasProperty('cachePath')) {
            $property = $reflection->getProperty('cachePath');
            $property->setAccessible(true);
            $property->setValue($blade, $path);
        }
    }

    private function getBladeCompiledPath(object $blade): ?string
    {
        if (! $blade instanceof BladeCompiler) {
            return null;
        }

        $reflection = new \ReflectionObject($blade);

        if (! $reflection->hasProperty('cachePath')) {
            return null;
        }

        $property = $reflection->getProperty('cachePath');
        $property->setAccessible(true);

        $value = $property->getValue($blade);

        return is_string($value) ? $value : null;
    }

    private function formatLocalizedDate(mixed $value, string $format): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            if ($value instanceof CarbonInterface) {
                return $value->locale('id')->translatedFormat($format);
            }

            return Carbon::parse((string) $value)->locale('id')->translatedFormat($format);
        } catch (Throwable) {
            return null;
        }
    }

    private function formatStatusLabel(?string $status): string
    {
        return match ($status) {
            'approved' => 'Disetujui',
            'disetujui' => 'Disetujui',
            'rejected' => 'Ditolak',
            'revisi' => 'Revisi',
            default => 'Menunggu Review Mitra',
        };
    }

    private function formatTimeValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value->format('H:i');
        }

        $time = trim((string) $value);

        if (preg_match('/^\d{2}:\d{2}/', $time, $matches) === 1) {
            return substr($matches[0], 0, 5);
        }

        try {
            return Carbon::parse($time)->format('H:i');
        } catch (Throwable) {
            return $time;
        }
    }

    /**
     * @return array{text: string, items: array<int, string>}
     */
    private function buildStructuredContent(?string $value): array
    {
        $text = trim(preg_replace("/\r\n?/", "\n", (string) ($value ?? '')) ?? '');

        if ($text === '') {
            return [
                'text' => '-',
                'items' => [],
            ];
        }

        return [
            'text' => $text,
            'items' => $this->extractOrderedListItems($text),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function extractOrderedListItems(string $text): array
    {
        $lines = preg_split("/\n+/", $text) ?: [];
        $items = [];
        $currentIndex = -1;
        $hasNumberedLine = false;

        foreach ($lines as $line) {
            $trimmedLine = trim($line);

            if ($trimmedLine === '') {
                continue;
            }

            if (preg_match('/^\d+[\.\)]\s*(.+)$/', $trimmedLine, $matches) === 1) {
                $hasNumberedLine = true;
                $items[] = trim($matches[1]);
                $currentIndex = count($items) - 1;

                continue;
            }

            if ($currentIndex === -1) {
                return [];
            }

            $items[$currentIndex] = trim($items[$currentIndex].' '.$trimmedLine);
        }

        if (! $hasNumberedLine || count($items) < 2) {
            return [];
        }

        return array_values(array_filter($items, fn (string $item) => $item !== ''));
    }
}
