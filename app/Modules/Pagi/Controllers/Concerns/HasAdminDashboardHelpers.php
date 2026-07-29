<?php

namespace App\Modules\Pagi\Controllers\Concerns;

use Carbon\Carbon;

trait HasAdminDashboardHelpers
{
    protected static string $DEFAULT_STUDENT_EMAIL = 'student@fmikom.ac.id';

    protected static string $DEFAULT_REPORTER_EMAIL = 'reporter@fmikom.ac.id';

    /**
     * Auto-seed real portfolio & moderation data linked together in the database
     */
    protected function seedPagiDemoData(): void
    {
        // Auto-seeding disabled to preserve clean database state
    }

    protected function getStorageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return str_starts_with($path, 'http') ? $path : asset('storage/'.$path);
    }

    protected function getReportReasonLabel(?string $reason): string
    {
        return match ($reason) {
            'copyright_violation' => 'Pelanggaran Hak Cipta',
            'inappropriate_content' => 'Konten Tidak Pantas',
            'spam' => 'Spam',
            'harassment' => 'Pelecehan',
            'misinformation' => 'Misinformasi',
            default => 'Lainnya',
        };
    }

    protected function formatChartLabel(Carbon $date, int $offsetFromEnd, int $totalDays): string
    {
        if ($totalDays <= 30) {
            return $date->translatedFormat('d M');
        }

        // For 90-day range: only label every 10th day and the first/last
        $isLabelDay = ($offsetFromEnd % 10 === 0)
            || $offsetFromEnd === $totalDays - 1
            || $offsetFromEnd === 0;

        return $isLabelDay ? $date->translatedFormat('d M') : '';
    }
}
