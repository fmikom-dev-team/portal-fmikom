<?php

namespace App\Modules\Wims\Services\Mahasiswa\Logbook;

use App\Models\LogbookMagang;
use App\Models\PendaftaranMagang;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Carbon\CarbonInterface;
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
            return [
                'number' => $index + 1,
                'date' => $this->formatLocalizedDate($logbook->tanggal, 'd F Y'),
                'start_time' => $this->formatTimeValue($logbook->jam_mulai) ?? '-',
                'end_time' => $this->formatTimeValue($logbook->jam_selesai) ?? '-',
                'activity' => $logbook->aktivitas_harian ?? '-',
                'competency' => $logbook->kompetensi_dicapai ?? '-',
                'status' => $this->formatStatusLabel($logbook->status),
                'mentor_note' => $logbook->catatan_mitra ?? $logbook->catatan_dosen ?? '-',
            ];
        });

        return Pdf::loadView('pdf.logbook-history', [
            'student' => [
                'name' => $student?->name ?? '-',
                'nim' => $student?->nim_nip ?: $student?->nomor_induk ?: '-',
                'program_studi' => $programStudi?->nama ?? '-',
            ],
            'internship' => [
                'company' => $registration->perusahaan?->nama ?? '-',
                'period' => $registration->tanggal_mulai && $registration->tanggal_selesai
                    ? $registration->tanggal_mulai->locale('id')->translatedFormat('d M Y') . ' - ' . $registration->tanggal_selesai->locale('id')->translatedFormat('d M Y')
                    : '-',
                'supervisor_lecturer' => $registration->dosenPembimbing?->name ?? '-',
                'mentor' => $registration->perusahaan?->user?->name ?? '-',
            ],
            'rows' => $rows,
        ])
            ->setPaper('a4', 'landscape')
            ->download('logbook-pkl-periode-terakhir-' . now()->format('Y-m-d') . '.pdf');
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
}
