<?php

namespace App\Services\Wims\Dosen;

use App\Models\User;
use App\Services\MonitoringAlertService;
class DosenDashboardPageService
{
    public function __construct(
        private readonly MonitoringAlertService $monitoringAlertService,
        private readonly DosenMonitoringOverviewService $monitoringOverviewService,
    ) {
    }

    public function build(User $user): array
    {
        $today = now()->toDateString();
        $warnings = $this->monitoringAlertService->getWarningsForLecturer($user);
        $overview = $this->monitoringOverviewService->buildOverview($user, $today);
        $students = $overview['students'];
        $summary = $overview['summary'];

        return [
            'summary' => [
                'total_students' => $summary['total_students'] ?? $students->count(),
                'upcoming_students' => $summary['upcoming_students'] ?? $students->where('dashboard_phase', 'upcoming')->count(),
                'active_students' => $summary['active_students'] ?? $students->where('dashboard_phase', 'active')->count(),
                'completed_students' => $summary['completed_students'] ?? $students->where('dashboard_phase', 'completed')->count(),
                'needs_attention' => $warnings->count(),
                'not_present' => $summary['not_present'] ?? $students->where('attendance_status', 'alfa')->count(),
                'active_warnings' => $warnings->count(),
                'not_evaluated' => $summary['not_evaluated'] ?? $students
                    ->where('dashboard_phase', 'completed')
                    ->filter(fn (array $student) => ($student['objective_summary']['evaluation_status'] ?? 'not_assessed') !== 'submitted')
                    ->count(),
            ],
            'filters' => [
                'date' => $today,
            ],
            'students' => $students->all(),
            'warnings' => $warnings->all(),
        ];
    }
}
