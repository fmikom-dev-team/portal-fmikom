<?php

namespace App\Modules\Wims\Services\Mahasiswa\Attendance;

use App\Models\Magang\AbsensiMagang;
use App\Models\User;
use App\Modules\Wims\Services\Mahasiswa\Period\StudentPeriodResolverService;
use App\Modules\Wims\Services\Shared\Attendance\AttendanceSyncService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\Compilers\BladeCompiler;
use Symfony\Component\HttpFoundation\Response;

class AttendanceExportService
{
    public function __construct(
        private readonly AttendanceSyncService $attendanceSyncService,
        private readonly StudentPeriodResolverService $studentPeriodResolverService,
    ) {}

    public function download(User $user, string $scope, ?int $registrationId = null): Response
    {
        app()->setLocale('id');
        Carbon::setLocale('id');

        $student = $user->loadMissing('programStudi');
        $registrations = $this->studentPeriodResolverService->resolveRegistrations($user->id, [
            'perusahaan.user',
            'dosenPembimbing',
        ]);
        $this->attendanceSyncService->syncForRegistrations($registrations);

        $attendanceHistoryQuery = AbsensiMagang::query()
            ->with('pendaftaran.perusahaan')
            ->whereHas('pendaftaran', fn ($query) => $query->where('mahasiswa_id', $user->id))
            ->orderByDesc('tanggal')
            ->orderByDesc('id');

        $fileNamePrefix = 'riwayat-presensi';
        $headerRegistration = $registrations->first();

        if ($scope === 'current') {
            $currentRegistration = $this->studentPeriodResolverService->resolveSelectedRegistrationFromCollection($registrations, $registrationId);

            abort_if($currentRegistration === null, 404);

            $attendanceHistoryQuery->where('pendaftaran_id', $currentRegistration->id);
            $fileNamePrefix = 'riwayat-presensi-periode-ini';
            $headerRegistration = $currentRegistration;
        }

        $attendanceHistory = $attendanceHistoryQuery->get();
        $fileName = $fileNamePrefix.'-'.now()->format('Y-m-d').'.pdf';
        $exportRows = $attendanceHistory->map(function (AbsensiMagang $attendance, int $index) {
            return [
                'number' => $index + 1,
                'date' => $attendance->tanggal?->locale('id')->translatedFormat('d F Y') ?? '-',
                'status' => $this->formatAttendanceStatusLabel($attendance->status),
                'check_in' => $attendance->timestamp_masuk?->format('H:i:s') ?? '-',
                'check_out' => $attendance->timestamp_keluar?->format('H:i:s') ?? '-',
                'remark' => is_null($attendance->lokasi_valid)
                    ? '-'
                    : ($attendance->lokasi_valid ? 'Lokasi tervalidasi' : 'Lokasi belum tervalidasi'),
            ];
        });

        return $this->renderPdfWithIsolatedCompiledViews('pdf.attendance-history', [
            'student' => [
                'name' => $student->name ?? '-',
                'nim' => $student->nim_nip ?: $student->nomor_induk ?: '-',
                'program_studi' => $student->programStudi?->nama ?? '-',
            ],
            'internship' => [
                'company' => $headerRegistration?->perusahaan?->nama ?? '-',
                'period' => $headerRegistration?->periodLabel() ?? '-',
                'supervisor_lecturer' => $headerRegistration?->dosenPembimbing?->name ?? '-',
                'mentor' => $headerRegistration?->finalMentor()?->name ?? '-',
            ],
            'rows' => $exportRows,
        ], $fileName);
    }

    /**
     * Render PDF using a request-scoped compiled view directory to avoid
     * Windows file-lock collisions in storage/framework/views.
     */
    private function renderPdfWithIsolatedCompiledViews(string $view, array $data, string $fileName)
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

    private function formatAttendanceStatusLabel(?string $status): string
    {
        return match ($status) {
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alfa' => 'Alfa',
            default => $status ? ucfirst(str_replace('_', ' ', $status)) : '-',
        };
    }
}
