<?php

namespace App\Modules\Wims\Services\Admin;

use App\Models\Magang\LogbookMagang;
use App\Models\Magang\PendaftaranMagang;
use App\Modules\Wims\Services\Shared\Attendance\AttendanceSyncService;
use App\Modules\Wims\Services\Shared\Monitoring\MonitoringHistoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use Symfony\Component\HttpFoundation\Response;

class AdminMonitoringExportService
{
    public function __construct(
        private readonly AttendanceSyncService $attendanceSyncService,
        private readonly MonitoringHistoryService $monitoringHistoryService,
    ) {}

    public function downloadAttendance(PendaftaranMagang $pendaftaran): Response
    {
        $pendaftaran->loadMissing([
            'mahasiswa.programStudi',
            'perusahaan.user',
            'dosenPembimbing',
        ]);

        $this->attendanceSyncService->syncForRegistration($pendaftaran);

        $rows = collect($this->monitoringHistoryService->buildAttendanceTimeline($pendaftaran))
            ->values()
            ->map(function (array $attendance, int $index): array {
                return [
                    'number' => $index + 1,
                    'date' => $attendance['tanggal_label'] ?? '-',
                    'check_in' => $this->formatTimeValue($attendance['check_in'] ?? null) ?? '-',
                    'check_out' => $this->formatTimeValue($attendance['check_out'] ?? null) ?? '-',
                    'status' => $this->formatAttendanceStatusLabel($attendance['status'] ?? null),
                    'remark' => $attendance['keterangan'] ?? $this->formatAttendanceRemark($attendance['status'] ?? null),
                ];
            });

        return $this->renderPdfWithIsolatedCompiledViews('pdf.attendance-history', [
            'student' => [
                'name' => $pendaftaran->mahasiswa?->name ?? '-',
                'nim' => $pendaftaran->mahasiswa?->nim_nip ?: $pendaftaran->mahasiswa?->nomor_induk ?: '-',
                'program_studi' => $pendaftaran->mahasiswa?->programStudi?->nama ?? '-',
            ],
            'internship' => [
                'company' => $pendaftaran->perusahaan?->nama ?? '-',
                'period' => $pendaftaran->periodLabel() ?? '-',
                'supervisor_lecturer' => $pendaftaran->dosenPembimbing?->name ?? '-',
                'mentor' => $pendaftaran->finalMentor()?->name ?? '-',
            ],
            'rows' => $rows,
        ], $this->attendanceFileName());
    }

    public function downloadLogbook(PendaftaranMagang $pendaftaran): Response
    {
        $pendaftaran->loadMissing([
            'mahasiswa.programStudi',
            'perusahaan.user',
            'dosenPembimbing',
        ]);

        $rows = LogbookMagang::query()
            ->with('reviewedByMitra')
            ->where('pendaftaran_id', $pendaftaran->id)
            ->orderBy('tanggal')
            ->orderBy('jam_mulai')
            ->orderBy('id')
            ->get()
            ->map(function (LogbookMagang $logbook, int $index): array {
                $activityContent = $this->buildStructuredContent($logbook->aktivitas_harian);
                $competencyContent = $this->buildStructuredContent($logbook->kompetensi_dicapai);

                return [
                    'number' => $index + 1,
                    'date' => $this->formatLocalizedDate($logbook->tanggal, 'd F Y') ?? '-',
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
                'name' => $pendaftaran->mahasiswa?->name ?? '-',
                'nim' => $pendaftaran->mahasiswa?->nim_nip ?: $pendaftaran->mahasiswa?->nomor_induk ?: '-',
                'program_studi' => $pendaftaran->mahasiswa?->programStudi?->nama ?? '-',
            ],
            'internship' => [
                'company' => $pendaftaran->perusahaan?->nama ?? '-',
                'period' => $pendaftaran->periodLabel() ?? '-',
                'supervisor_lecturer' => $pendaftaran->dosenPembimbing?->name ?? '-',
                'mentor' => $pendaftaran->finalMentor()?->name ?? '-',
            ],
            'rows' => $rows,
        ], $this->logbookFileName());
    }

    /**
     * Render PDF using a request-scoped compiled view directory to avoid
     * Windows file-lock collisions in storage/framework/views.
     */
    private function renderPdfWithIsolatedCompiledViews(string $view, array $data, string $fileName): Response
    {
        $compiledPath = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR
            .'wims-pdf-views-'
            .Str::uuid();

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

    private function attendanceFileName(): string
    {
        return sprintf('riwayat-presensi-admin-pkl-%s.pdf', now()->format('Y-m-d'));
    }

    private function logbookFileName(): string
    {
        return sprintf('logbook-admin-pkl-%s.pdf', now()->format('Y-m-d'));
    }

    private function formatAttendanceStatusLabel(?string $status): string
    {
        return match ($status) {
            'hadir' => 'Hadir',
            'tepat_waktu' => 'Tepat Waktu',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alfa' => 'Alfa',
            'hari_libur' => 'Hari Libur',
            'bukan_hari_kerja' => 'Bukan Hari Kerja',
            default => $status ? ucfirst(str_replace('_', ' ', $status)) : '-',
        };
    }

    private function formatAttendanceRemark(?string $status): string
    {
        return match ($status) {
            'hari_libur' => 'Hari libur',
            'bukan_hari_kerja' => 'Bukan hari kerja',
            default => '-',
        };
    }

    private function formatLocalizedDate(mixed $value, string $format): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            if ($value instanceof Carbon) {
                return $value->locale('id')->translatedFormat($format);
            }

            return Carbon::parse((string) $value)->locale('id')->translatedFormat($format);
        } catch (\Throwable) {
            return null;
        }
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
        } catch (\Throwable) {
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
