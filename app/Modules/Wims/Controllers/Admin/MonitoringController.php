<?php

namespace App\Modules\Wims\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Magang\PendaftaranMagang;
use App\Modules\Wims\Services\Admin\AdminMonitoringExportService;
use App\Modules\Wims\Services\Admin\AdminMonitoringPageService;
use App\Modules\Wims\Services\Shared\Attendance\AttendanceSyncService;
use App\Modules\Wims\Services\Shared\Monitoring\MonitoringDetailService;
use App\Modules\Wims\Services\Shared\Monitoring\MonitoringHistoryService;
use App\Modules\Wims\Services\Shared\Monitoring\MonitoringRegistrationResolverService;
use App\Modules\Wims\Services\Shared\Monitoring\MonitoringSummaryService;
use App\Modules\Wims\Support\AssessmentSummary;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MonitoringController extends Controller
{
    public function __construct(
        private readonly AdminMonitoringPageService $adminMonitoringPageService,
        private readonly AttendanceSyncService $attendanceSyncService,
        private readonly MonitoringDetailService $monitoringDetailService,
        private readonly MonitoringHistoryService $monitoringHistoryService,
        private readonly MonitoringRegistrationResolverService $monitoringRegistrationResolverService,
        private readonly MonitoringSummaryService $monitoringSummaryService,
        private readonly AdminMonitoringExportService $adminMonitoringExportService,
    ) {}

    public function index(Request $request): Response
    {
        return Inertia::render('Modules/Wims/Admin/Monitoring/Index', $this->adminMonitoringPageService->build($request));
    }

    public function show(Request $request, PendaftaranMagang $pendaftaran): Response
    {
        $pendaftaran->loadMissing([
            'mahasiswa.programStudi',
            'perusahaan.user',
            'dosenPembimbing',
            'assessmentSubmissions' => fn ($builder) => $builder
                ->whereIn('assessor_role', ['dosen', 'mitra'])
                ->orderByDesc('submitted_at')
                ->orderByDesc('updated_at')
                ->orderByDesc('id'),
        ]);

        $this->attendanceSyncService->syncForRegistration($pendaftaran);

        $todayDate = now()->toDateString();
        $periodStart = optional($pendaftaran->tanggal_mulai)->toDateString();
        $periodEnd = optional($pendaftaran->tanggal_selesai)->toDateString();
        $selectedDate = $this->monitoringRegistrationResolverService->resolveSelectedDate(
            $request->query('date'),
            $periodStart,
            $periodEnd,
            $this->monitoringRegistrationResolverService->resolveMonitoringReferenceDate($pendaftaran),
        );

        $attendanceHistory = $this->monitoringHistoryService->buildAttendanceTimeline($pendaftaran);
        $logbookHistory = $this->monitoringHistoryService->buildLogbookHistory($pendaftaran);
        $assessment = AssessmentSummary::fromSubmissions($pendaftaran->assessmentSubmissions);
        $attendanceSummary = $this->monitoringSummaryService->buildAttendanceSummary($pendaftaran);
        $logbookSummary = $this->monitoringSummaryService->buildLogbookSummary($pendaftaran);

        return Inertia::render('Modules/Wims/Admin/Monitoring/Show', $this->monitoringDetailService->buildPayload(
            $pendaftaran,
            $todayDate,
            $selectedDate,
            $attendanceHistory,
            $logbookHistory,
            $assessment,
            $attendanceSummary,
            $logbookSummary,
        ));
    }

    public function downloadAttendance(PendaftaranMagang $pendaftaran)
    {
        return $this->adminMonitoringExportService->downloadAttendance($pendaftaran);
    }

    public function downloadLogbook(PendaftaranMagang $pendaftaran)
    {
        return $this->adminMonitoringExportService->downloadLogbook($pendaftaran);
    }
}
