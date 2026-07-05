<?php

namespace App\Modules\Wims\Services\Mahasiswa\Logbook;

use App\Models\Magang\LogbookMagang;
use App\Models\Magang\LogbookPhoto;
use App\Models\Magang\PendaftaranMagang;
use App\Modules\Wims\Services\Mahasiswa\Period\StudentPeriodResolverService;
use App\Support\PublicStorageUrl;
use Illuminate\Support\Carbon;
use Throwable;

class LogbookPageService
{
    public function __construct(
        private readonly StudentPeriodResolverService $studentPeriodResolverService,
    ) {}

    public function build(int $userId, ?int $selectedRegistrationId = null): array
    {
        $registrations = $this->studentPeriodResolverService->resolveRegistrations($userId, ['perusahaan']);
        $pendaftaran = $this->studentPeriodResolverService->resolveSelectedRegistrationFromCollection($registrations, $selectedRegistrationId);
        $periods = $this->studentPeriodResolverService->buildPeriodOptions($registrations, $pendaftaran?->id);

        $todayLogbook = LogbookMagang::with('photos')
            ->where('pendaftaran_id', $pendaftaran?->id)
            ->whereDate('tanggal', now()->toDateString())
            ->latest('id')
            ->first();

        $logbooks = LogbookMagang::with('photos')
            ->where('pendaftaran_id', $pendaftaran?->id)
            ->latest('tanggal')
            ->latest('id')
            ->get();

        $canSubmitToday = $this->canSubmitToday($pendaftaran);

        return [
            'selected_period_id' => $pendaftaran?->id,
            'periods' => $periods,
            'todayLabel' => now()->locale('id')->translatedFormat('l, d F Y'),
            'hasPendaftaran' => $pendaftaran !== null,
            'canSubmitToday' => $canSubmitToday,
            'submitBlockedMessage' => $canSubmitToday
                ? null
                : $this->blockedMessage($pendaftaran),
            'todayLogbook' => $todayLogbook ? $this->transformLogbook($todayLogbook) : null,
            'logbooks' => $logbooks
                ->map(fn (LogbookMagang $logbook) => $this->transformLogbook($logbook))
                ->values()
                ->all(),
        ];
    }

    public function resolvePendaftaran(int $userId, ?int $selectedRegistrationId = null): ?PendaftaranMagang
    {
        $registrations = $this->studentPeriodResolverService->resolveRegistrations($userId, ['perusahaan']);

        return $this->studentPeriodResolverService->resolveSelectedRegistrationFromCollection($registrations, $selectedRegistrationId);
    }

    public function canSubmitToday(?PendaftaranMagang $pendaftaran): bool
    {
        return $pendaftaran?->allowsDailyActivity() ?? false;
    }

    public function blockedMessage(?PendaftaranMagang $pendaftaran): string
    {
        if (! $pendaftaran) {
            return 'Data pendaftaran magang tidak ditemukan.';
        }

        if (! $pendaftaran->isActivatedByAdmin()) {
            return 'Logbook belum dibuka. Tunggu sampai admin menetapkan penempatan final dan mengaktifkan PKL/magang Anda.';
        }

        $periodeMulai = $pendaftaran->tanggal_mulai?->locale('id')->translatedFormat('d M Y') ?? '-';
        $periodeSelesai = $pendaftaran->tanggal_selesai?->locale('id')->translatedFormat('d M Y') ?? '-';

        return "Logbook hanya dapat diisi sesuai periode PKL yang diajukan, yaitu {$periodeMulai} s/d {$periodeSelesai}.";
    }

    public function transformLogbook(LogbookMagang $logbook): array
    {
        return [
            'id' => $logbook->id,
            'tanggal' => $logbook->tanggal?->toDateString(),
            'tanggal_label' => $logbook->tanggal?->locale('id')->translatedFormat('d M Y'),
            'jam_mulai' => $this->formatTimeValue($logbook->jam_mulai),
            'jam_selesai' => $this->formatTimeValue($logbook->jam_selesai),
            'aktivitas_harian' => $logbook->aktivitas_harian,
            'kompetensi_dicapai' => $logbook->kompetensi_dicapai,
            'catatan_mitra' => $logbook->catatan_mitra ?? $logbook->catatan_dosen,
            'status' => $logbook->status,
            'is_revisable' => $logbook->status === 'revisi',
            'photos' => $logbook->photos
                ->map(fn (LogbookPhoto $photo) => [
                    'id' => $photo->id,
                    'file_path' => $photo->file_path,
                    'url' => PublicStorageUrl::signed($photo->file_path),
                ])
                ->values()
                ->all(),
        ];
    }

    public function formatTimeValue(mixed $value): ?string
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
