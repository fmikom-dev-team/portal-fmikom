<?php

namespace App\Modules\Trace\Actions;

use App\Models\Pagi\PagiCv;
use App\Models\Pagi\PagiWork;
use App\Models\Tracer\JobApplicant;
use App\Models\Tracer\JobListing;
use App\Models\Tracer\ProfilAlumni;
use Illuminate\Support\Facades\DB;

class ApplyToJobAction
{
    /**
     * @return array{success?: bool, error?: string}
     */
    public function execute(JobListing $job, ProfilAlumni $alumni, array $data, int $userId): array
    {
        // 1. Deadline check
        if ($job->deadline && now()->greaterThan($job->deadline)) {
            return ['error' => 'Batas waktu lamaran sudah berakhir.'];
        }

        // 2. Validate CV ownership
        $cvIds = $data['attached_cv_ids'] ?? [];
        if (! empty($cvIds)) {
            $validCvCount = PagiCv::where('user_id', $userId)
                ->whereIn('id', $cvIds)
                ->count();
            if ($validCvCount !== count($cvIds)) {
                return ['error' => 'CV yang dipilih tidak valid.'];
            }
        }

        // 3. Validate portfolio ownership
        $portfolioIds = $data['attached_portfolio_ids'] ?? [];
        if (! empty($portfolioIds)) {
            $validWorkCount = PagiWork::where('user_id', $userId)
                ->whereIn('id', $portfolioIds)
                ->count();
            if ($validWorkCount !== count($portfolioIds)) {
                return ['error' => 'Portfolio yang dipilih tidak valid.'];
            }
        }

        // 4. Transaction with lockForUpdate
        $result = DB::transaction(function () use ($data, $job, $alumni, $cvIds, $portfolioIds) {
            $alreadyApplied = JobApplicant::where('job_id', $job->id)
                ->where('alumni_id', $alumni->id)
                ->lockForUpdate()
                ->exists();

            if ($alreadyApplied) {
                return ['error' => 'Anda sudah melamar pada lowongan ini.'];
            }

            JobApplicant::create([
                'job_id' => $job->id,
                'alumni_id' => $alumni->id,
                'cover_letter' => $data['cover_letter'] ?? null,
                'attached_cv_ids' => ! empty($cvIds) ? $cvIds : null,
                'attached_portfolio_ids' => ! empty($portfolioIds) ? $portfolioIds : null,
                'status' => 'applied',
                'applied_at' => now(),
            ]);

            return ['success' => true];
        });

        return $result;
    }
}
