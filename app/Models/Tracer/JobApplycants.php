<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tracer\ProfilAlumni;

class JobApplycants extends Model
{
    //
    protected $table = 'job_applycants';

    protected $fillable = [
        'job_id', 'alumni_id', 'cover_letter', 'status', 'applied_at'
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
