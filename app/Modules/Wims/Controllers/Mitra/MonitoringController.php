<?php

namespace App\Modules\Wims\Controllers\Mitra;

use App\Http\Controllers\Controller;
use App\Models\Magang\PerusahaanMitra;
use App\Modules\Wims\Services\Shared\Attendance\AttendanceSyncService;
use App\Modules\Wims\Services\Shared\Monitoring\MonitoringDetailService;
use App\Modules\Wims\Services\Shared\Monitoring\MonitoringHistoryService;
use App\Modules\Wims\Services\Shared\Monitoring\MonitoringRegistrationResolverService;
use App\Modules\Wims\Services\Shared\Monitoring\MonitoringSummaryService;
use App\Modules\Wims\Services\Mitra\MitraMonitoringOverviewService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MonitoringController extends Controller
{
    public function __construct(
        private readonly AttendanceSyncService $attendanceSyncService,
        private readonly MitraMonitoringOverviewService $monitoringOverviewService,
        private readonly MonitoringRegistrationResolverService $monitoringRegistrationResolverService,
        private readonly MonitoringHistoryService $monitoringHistoryService,
        private readonly MonitoringSummaryService $monitoringSummaryService,
        private readonly MonitoringDetailService $monitoringDetailService,
    ) {
    }

    public function index(Request $request): Response
    {
        $company = PerusahaanMitra::query()
            ->where('user_id', $request->user()->id)
            ->first();

        $overview = $this->monitoringOverviewService->buildOverview($company, now()->toDateString());
        $allowedStatuses = ['aktif', 'selesai', 'perlu-tindak-lanjut', 'revisi', 'alfa', 'belum-dinilai'];
        $initialStatus = (string) $request->query('status', '');

        if (! in_array($initialStatus, $allowedStatuses, true)) {
            $initialStatus = '';
        }

        return Inertia::render('Wims/Mitra/Monitoring/Index', [
            'summary' => $overview['summary'],
            'students' => $overview['students']->all(),
            'initialStatus' => $initialStatus,
        ]);
    }

    public function show(Request $request, string $mahasiswaId): Response
    {
        $company = PerusahaanMitra::query()
            ->where('user_id', $request->user()->id)
            ->first();

        abort_unless($company !== null, 403);

        $mahasiswaId = (int) $mahasiswaId;
        $todayDate = now()->toDateString();
        $requestedDate = $this->monitoringRegistrationResolverService->normalizeDateInput($request->query('date')) ?? $todayDate;

        $pendaftaran = $this->monitoringRegistrationResolverService->resolveForCompany($company, $mahasiswaId, $requestedDate);
        abort_unless($pendaftaran !== null, 403);

        $this->attendanceSyncService->syncForRegistration($pendaftaran);

        $periodStart = optional($pendaftaran->tanggal_mulai)->toDateString();
        $periodEnd = optional($pendaftaran->tanggal_selesai)->toDateString();
        $selectedDate = $this->monitoringRegistrationResolverService->resolveSelectedDate(
            $request->query('date'),
            $periodStart,
            $periodEnd,
            $todayDate,
        );

        $attendanceHistory = $this->monitoringHistoryService->buildAttendanceTimeline($pendaftaran);
        $logbookHistory = $this->monitoringHistoryService->buildLogbookHistory($pendaftaran);
        $assessment = $this->monitoringSummaryService->buildAssessmentSummary($pendaftaran, $request->user(), 'mitra');
        $attendanceSummary = $this->monitoringSummaryService->buildAttendanceSummary($pendaftaran);
        $logbookSummary = $this->monitoringSummaryService->buildLogbookSummary($pendaftaran);

        return Inertia::render('Wims/Mitra/Monitoring/Show', $this->monitoringDetailService->buildPayload(
            $pendaftaran,
            $todayDate,
            $selectedDate,
            $attendanceHistory,
            $logbookHistory,
            $assessment,
            $attendanceSummary,
            $logbookSummary,
            false,
            null,
        ));
    }
}
