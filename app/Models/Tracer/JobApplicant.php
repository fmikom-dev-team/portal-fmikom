<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tracer\ProfilAlumni;

class JobApplicant extends Model
{
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
