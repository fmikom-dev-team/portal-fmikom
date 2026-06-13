<?php

namespace App\Http\Controllers\Wims\Dosen;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AttendanceSyncService;
use App\Services\MonitoringAlertService;
use App\Services\Wims\Dosen\DosenMonitoringOverviewService;
use App\Services\Wims\Shared\Monitoring\MonitoringDetailService;
use App\Services\Wims\Shared\Monitoring\MonitoringHistoryService;
use App\Services\Wims\Shared\Monitoring\MonitoringRegistrationResolverService;
use App\Services\Wims\Shared\Monitoring\MonitoringSummaryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MonitoringController extends Controller
{
    public function __construct(
        private readonly AttendanceSyncService $attendanceSyncService,
        private readonly MonitoringAlertService $monitoringAlertService,
        private readonly DosenMonitoringOverviewService $monitoringOverviewService,
        private readonly MonitoringRegistrationResolverService $monitoringRegistrationResolverService,
        private readonly MonitoringHistoryService $monitoringHistoryService,
        private readonly MonitoringSummaryService $monitoringSummaryService,
        private readonly MonitoringDetailService $monitoringDetailService,
    ) {
    }

    public function index(Request $request): Response
    {
        /** @var User $currentUser */
        $currentUser = $request->user();
        $today = now()->toDateString();
        $overview = $this->monitoringOverviewService->buildOverview($currentUser, $today);
        $warnings = $this->monitoringAlertService->getWarningsForLecturer($currentUser);
        $warningLookup = $warnings->keyBy('student_id');

        $students = collect($overview['students'])
            ->map(function (array $student) use ($warningLookup) {
                $warning = $warningLookup->get($student['id']);

                return [
                    ...$student,
                    'needs_attention' => $warning !== null
                        || $student['attendance_status'] === 'alfa'
                        || in_array($student['logbook_status'], ['belum_isi', 'revisi'], true),
                    'warning' => $warning,
                ];
            })
            ->values();

        return Inertia::render('Wims/Dosen/Monitoring/Index', [
            'students' => $students->all(),
            'initialStatus' => $request->string('status')->toString(),
        ]);
    }

    public function show(Request $request, string $mahasiswaId): Response
    {
        /** @var User $currentUser */
        $currentUser = $request->user();
        $mahasiswaId = (int) $mahasiswaId;
        $mode = $request->input('mode', 'view');
        $requestedPendaftaranId = $request->integer('pendaftaran');
        $todayDate = now()->toDateString();
        $selectedDateInput = $request->input('date');
        $requestedDate = $this->monitoringRegistrationResolverService->normalizeDateInput($selectedDateInput) ?? $todayDate;

        $pendaftaran = $this->monitoringRegistrationResolverService->resolveForLecturer(
            $currentUser,
            $mahasiswaId,
            $requestedDate,
            $requestedPendaftaranId > 0 ? $requestedPendaftaranId : null,
        );

        abort_unless($pendaftaran !== null, 403);

        $periodStart = optional($pendaftaran->tanggal_mulai)->toDateString();
        $periodEnd = optional($pendaftaran->tanggal_selesai)->toDateString();
        $selectedDate = $this->monitoringRegistrationResolverService->resolveSelectedDate(
            $selectedDateInput,
            $periodStart,
            $periodEnd,
            $this->monitoringRegistrationResolverService->resolveMonitoringReferenceDate($pendaftaran),
        );

        $this->attendanceSyncService->syncForRegistration($pendaftaran);

        $attendanceHistory = $this->monitoringHistoryService->buildAttendanceTimeline($pendaftaran);
        $logbookHistory = $this->monitoringHistoryService->buildLogbookHistory($pendaftaran, true);
        $assessment = $this->monitoringSummaryService->buildAssessmentSummary($pendaftaran, $currentUser, 'dosen');
        $attendanceSummary = $this->monitoringSummaryService->buildAttendanceSummary($pendaftaran);
        $logbookSummary = $this->monitoringSummaryService->buildLogbookSummary($pendaftaran);

        return Inertia::render('Wims/Dosen/Monitoring/Show', $this->monitoringDetailService->buildPayload(
            $pendaftaran,
            $todayDate,
            $selectedDate,
            $attendanceHistory,
            $logbookHistory,
            $assessment,
            $attendanceSummary,
            $logbookSummary,
            true,
            $mode,
        ));
    }
}
