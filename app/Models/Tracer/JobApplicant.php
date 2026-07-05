<?php

namespace App\Models\Tracer;

use App\Modules\Trace\Services\TraceCacheService;
use Illuminate\Database\Eloquent\Model;

class JobApplicant extends Model
{
    protected static function booted(): void
    {
        static::saved(function ($applicant) {
            TraceCacheService::forgetDashboardCaches(userId: $applicant->alumni?->user_id);
            TraceCacheService::forgetJobCaches(mitraId: $applicant->jobListing?->mitra_id);
        });

        static::deleted(function ($applicant) {
            TraceCacheService::forgetDashboardCaches(userId: $applicant->alumni?->user_id);
            TraceCacheService::forgetJobCaches(mitraId: $applicant->jobListing?->mitra_id);
        });
    }

    protected $table = 'job_applicants';

    protected $fillable = [
        'job_id', 'alumni_id', 'cover_letter',
        'attached_cv_ids', 'attached_portfolio_ids',
        'status', 'applied_at',
        'reviewer_note', 'reviewed_at',
    ];

    protected $casts = [
        'attached_cv_ids' => 'array',
        'attached_portfolio_ids' => 'array',
        'applied_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function jobListing()
    {
        return $this->belongsTo(JobListing::class, 'job_id');
    }

    public function alumni()
    {
        return $this->belongsTo(ProfilAlumni::class, 'alumni_id');
    }
}
