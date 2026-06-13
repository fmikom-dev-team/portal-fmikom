<?php

namespace App\Modules\Wims\Services\Admin;

use App\Models\Magang\PendaftaranMagang;
use App\Models\Magang\PerusahaanMitra;
use App\Models\User;
use App\Services\AttendanceSyncService;
use App\Modules\Wims\Support\AssessmentSummary;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminMonitoringPageService
{
    public function __construct(
        private readonly AttendanceSyncService $attendanceSyncService,
    ) {
    }

    public function build(Request $request): array
    {
        $status = (string) $request->string('status', 'all');
        $search = trim((string) $request->string('search', ''));
        $companyId = $request->integer('company_id') ?: null;
        $dosenId = $request->integer('dosen_id') ?: null;

        $query = PendaftaranMagang::query()
            ->with([
                'mahasiswa',
                'perusahaan',
                'dosenPembimbing',
                'assessmentSubmissions' => fn ($builder) => $builder
                    ->whereIn('assessor_role', ['dosen', 'mitra'])
                    ->orderByDesc('submitted_at')
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id'),
            ])
            ->orderByRaw("CASE
                WHEN status = 'aktif' THEN 0
                WHEN status = 'approved' THEN 1
                WHEN status = 'selesai' THEN 2
                WHEN status = 'pending' THEN 3
                ELSE 4
            END")
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id');

        if ($status !== '' && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder->whereHas('mahasiswa', function ($mahasiswaQuery) use ($search): void {
                    $mahasiswaQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nim_nip', 'like', "%{$search}%")
                        ->orWhere('nomor_induk', 'like', "%{$search}%");
                })->orWhereHas('perusahaan', function ($companyQuery) use ($search): void {
                    $companyQuery->where('nama', 'like', "%{$search}%");
                })->orWhereHas('dosenPembimbing', function ($dosenQuery) use ($search): void {
                    $dosenQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($companyId) {
            $query->where('perusahaan_id', $companyId);
        }

        if ($dosenId) {
            $query->where('dosen_pembimbing_id', $dosenId);
        }

        $registrations = $query
            ->paginate(10)
            ->withQueryString();

        $this->attendanceSyncService->syncForRegistrations($registrations->getCollection());

        $registrations->through(function (PendaftaranMagang $pendaftaran): array {
            $latestAttendance = $pendaftaran->absensis()
                ->latest('tanggal')
                ->latest('id')
                ->first();

            $latestLogbook = $pendaftaran->logbooks()
                ->latest('tanggal')
                ->latest('id')
                ->first();
            $assessmentSummary = AssessmentSummary::fromSubmissions($pendaftaran->assessmentSubmissions);

            return [
                'id' => $pendaftaran->id,
                'student_name' => $pendaftaran->mahasiswa?->name,
                'student_email' => $pendaftaran->mahasiswa?->email,
                'student_identity' => $pendaftaran->mahasiswa?->nim_nip ?: $pendaftaran->mahasiswa?->nomor_induk,
                'status' => $pendaftaran->status,
                'company_name' => $pendaftaran->perusahaan?->nama,
                'dosen_name' => $pendaftaran->dosenPembimbing?->name,
                'period_label' => $this->formatDateRange($pendaftaran->tanggal_mulai, $pendaftaran->tanggal_selesai),
                'attendance' => [
                    'status' => $latestAttendance?->status,
                    'label' => $this->attendanceStatusLabel($latestAttendance?->status, $latestAttendance !== null),
                    'date' => $latestAttendance?->tanggal?->translatedFormat('d M Y'),
                ],
                'logbook' => [
                    'status' => $latestLogbook?->status,
                    'label' => $this->logbookStatusLabel($latestLogbook?->status, $latestLogbook !== null),
                    'date' => $latestLogbook?->tanggal?->translatedFormat('d M Y'),
                ],
                'report' => [
                    'uploaded' => filled($pendaftaran->laporan_akhir_path),
                    'label' => filled($pendaftaran->laporan_akhir_path) ? 'Sudah diunggah' : 'Belum diunggah',
                    'uploaded_at' => $pendaftaran->laporan_akhir_uploaded_at?->translatedFormat('d M Y H:i'),
                ],
                'evaluation' => [
                    'nilai_akhir' => $assessmentSummary['dosen']['score'],
                    'status_penilaian' => $this->mapLegacyAssessmentStatus($assessmentSummary['status_key']),
                    'status_key' => $assessmentSummary['status_key'],
                    'status_label' => $assessmentSummary['status_label'],
                    'label' => $this->resolveAssessmentLabel($assessmentSummary),
                    'dosen_score' => $assessmentSummary['dosen']['score'],
                    'mitra_score' => $assessmentSummary['mitra']['score'],
                    'is_complete' => $assessmentSummary['is_complete'],
                ],
            ];
        });

        return [
            'filters' => [
                'status' => $status,
                'search' => $search,
                'company_id' => $companyId,
                'dosen_id' => $dosenId,
            ],
            'summary' => [
                'all' => PendaftaranMagang::count(),
                'active' => PendaftaranMagang::where('status', 'aktif')->count(),
                'completed' => PendaftaranMagang::where('status', 'selesai')->count(),
                'reports_uploaded' => PendaftaranMagang::whereNotNull('laporan_akhir_path')->count(),
                'final_scores' => PendaftaranMagang::whereHas('assessmentSubmissions', function ($builder): void {
                    $builder
                        ->where('assessor_role', 'dosen')
                        ->where('status', 'submitted');
                })->count(),
            ],
            'registrations' => $registrations,
            'options' => [
                'companies' => PerusahaanMitra::query()
                    ->orderBy('nama')
                    ->get(['id', 'nama'])
                    ->map(fn (PerusahaanMitra $company) => [
                        'id' => $company->id,
                        'label' => $company->nama,
                    ])
                    ->values()
                    ->all(),
                'dosen' => User::query()
                    ->whereHas('role', fn ($query) => $query->where('slug', 'dosen'))
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn (User $dosen) => [
                        'id' => $dosen->id,
                        'label' => $dosen->name,
                    ])
                    ->values()
                    ->all(),
            ],
        ];
    }

    private function formatDateRange(mixed $startDate, mixed $endDate): string
    {
        return sprintf(
            '%s - %s',
            $this->formatDate($startDate),
            $this->formatDate($endDate),
        );
    }

    private function formatDate(mixed $date): string
    {
        if (blank($date)) {
            return '-';
        }

        if ($date instanceof Carbon) {
            return $date->translatedFormat('d M Y');
        }

        return Carbon::parse($date)->translatedFormat('d M Y');
    }

    private function attendanceStatusLabel(?string $status, bool $hasRecord): string
    {
        if (! $hasRecord) {
            return 'Belum ada absensi';
        }

        return match ($status) {
            'hadir', 'tepat_waktu' => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alfa' => 'Alfa',
            default => ucfirst(str_replace('_', ' ', (string) $status)),
        };
    }

    private function logbookStatusLabel(?string $status, bool $hasRecord): string
    {
        if (! $hasRecord) {
            return 'Belum ada logbook';
        }

        return match ($status) {
            'approved', 'disetujui' => 'Disetujui',
            'revisi' => 'Perlu revisi',
            'rejected' => 'Ditolak',
            default => 'Menunggu review',
        };
    }

    private function mapLegacyAssessmentStatus(?string $statusKey): ?string
    {
        return match ($statusKey) {
            'submitted' => 'selesai',
            'final_dosen' => 'final_dosen',
            'final_mitra' => 'final_mitra',
            'draft' => 'draft',
            default => null,
        };
    }

    private function resolveAssessmentLabel(array $assessmentSummary): string
    {
        if (($assessmentSummary['is_complete'] ?? false) === true) {
            return 'Nilai dosen dan mitra tersedia';
        }

        if (($assessmentSummary['dosen']['score'] ?? null) !== null) {
            return 'Nilai dosen tersedia';
        }

        if (($assessmentSummary['mitra']['score'] ?? null) !== null) {
            return 'Nilai mitra tersedia';
        }

        if (($assessmentSummary['status_key'] ?? null) === 'draft') {
            return 'Draft penilaian';
        }

        return 'Belum ada penilaian.';
    }
}
