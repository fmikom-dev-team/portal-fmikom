<?php

namespace App\Modules\Wims\Services\Shared\Monitoring;

use App\Models\Magang\AbsensiMagang;
use App\Models\Magang\LogbookMagang;
use App\Models\Magang\PendaftaranMagang;
use App\Support\PublicStorageUrl;
use Illuminate\Support\Carbon;

class MonitoringDetailService
{
    public function __construct(
        private readonly MonitoringHistoryService $monitoringHistoryService,
    ) {
    }

    public function buildPayload(
        PendaftaranMagang $pendaftaran,
        string $todayDate,
        string $selectedDate,
        array $attendanceHistory,
        array $logbookHistory,
        array $assessment,
        array $attendanceSummary,
        array $logbookSummary,
        bool $preferDosenNote = false,
        ?string $mode = null,
    ): array {
        $periodStart = optional($pendaftaran->tanggal_mulai)->toDateString();
        $periodEnd = optional($pendaftaran->tanggal_selesai)->toDateString();

        $todayAttendance = AbsensiMagang::query()
            ->where('pendaftaran_id', $pendaftaran->id)
            ->whereDate('tanggal', $todayDate)
            ->latest('id')
            ->first();

        $todayLogbook = LogbookMagang::query()
            ->with(['photos', 'reviewedByMitra'])
            ->where('pendaftaran_id', $pendaftaran->id)
            ->whereDate('tanggal', $todayDate)
            ->latest('id')
            ->first();

        $selectedAttendance = AbsensiMagang::query()
            ->where('pendaftaran_id', $pendaftaran->id)
            ->whereDate('tanggal', $selectedDate)
            ->latest('id')
            ->first();

        $selectedLogbook = LogbookMagang::query()
            ->with(['photos', 'reviewedByMitra'])
            ->where('pendaftaran_id', $pendaftaran->id)
            ->whereDate('tanggal', $selectedDate)
            ->latest('id')
            ->first();

        $payload = [
            'student' => [
                'id' => $pendaftaran->mahasiswa?->id,
                'name' => $pendaftaran->mahasiswa?->name,
                'nim' => $pendaftaran->mahasiswa?->nim_nip ?: $pendaftaran->mahasiswa?->nomor_induk,
                'company' => [
                    'id' => $pendaftaran->perusahaan?->id,
                    'name' => $pendaftaran->perusahaan?->nama,
                ],
                'pendaftaran_id' => $pendaftaran->id,
                'status_pendaftaran' => $pendaftaran->status,
                'is_ready_for_assessment' => $pendaftaran->isReadyForAssessment(),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ],
            'today' => [
                'date' => $todayDate,
                'attendance' => [
                    'check_in' => $todayAttendance?->timestamp_masuk?->toIso8601String(),
                    'check_out' => $todayAttendance?->timestamp_keluar?->toIso8601String(),
                    'check_in_photo_url' => $todayAttendance?->checkInPhotoUrl(),
                    'check_out_photo_url' => $todayAttendance?->checkOutPhotoUrl(),
                    'status' => $todayAttendance ? $this->monitoringHistoryService->resolveAttendanceStatus($todayAttendance) : null,
                    'note' => null,
                ],
                'logbook' => $this->transformLogbook($todayLogbook, $todayDate, $preferDosenNote),
                'images' => $todayLogbook?->photos
                    ?->map(fn ($photo) => [
                        'id' => $photo->id,
                        'url' => PublicStorageUrl::signed($photo->file_path),
                    ])
                    ->values()
                    ->all() ?? [],
            ],
            'selected' => [
                'date' => $selectedDate,
                'attendance' => [
                    'check_in' => $selectedAttendance?->timestamp_masuk?->toIso8601String(),
                    'check_out' => $selectedAttendance?->timestamp_keluar?->toIso8601String(),
                    'check_in_photo_url' => $selectedAttendance?->checkInPhotoUrl(),
                    'check_out_photo_url' => $selectedAttendance?->checkOutPhotoUrl(),
                    'status' => $selectedAttendance ? $this->monitoringHistoryService->resolveAttendanceStatus($selectedAttendance) : null,
                    'note' => null,
                ],
                'logbook' => $this->transformLogbook($selectedLogbook, $selectedDate, $preferDosenNote),
                'images' => $selectedLogbook?->photos
                    ?->map(fn ($photo) => [
                        'id' => $photo->id,
                        'url' => PublicStorageUrl::signed($photo->file_path),
                    ])
                    ->values()
                    ->all() ?? [],
            ],
            'history' => [
                'attendance' => $attendanceHistory,
                'logbook' => $logbookHistory,
            ],
            'assessment' => $assessment,
            'summary' => [
                'attendance' => $attendanceSummary,
                'logbook' => $logbookSummary,
            ],
        ];

        if ($mode !== null) {
            $payload['mode'] = $mode;
        }

        return $payload;
    }

    private function transformLogbook(?LogbookMagang $logbook, string $date, bool $preferDosenNote): array
    {
        return [
            'id' => $logbook?->id,
            'tanggal' => $date,
            'tanggal_label' => $logbook?->tanggal?->translatedFormat('d M Y') ?? Carbon::parse($date)->translatedFormat('d M Y'),
            'aktivitas' => $logbook?->aktivitas_harian,
            'kompetensi' => $logbook?->kompetensi_dicapai,
            'status' => $logbook?->status,
            'catatan_mitra' => $preferDosenNote
                ? ($logbook?->catatan_mitra ?? $logbook?->catatan_dosen)
                : $logbook?->catatan_mitra,
            'reviewed_by_name' => $logbook?->reviewedByMitra?->name,
            'reviewed_at' => $logbook?->reviewed_by_mitra_at?->toIso8601String(),
        ];
    }
}
