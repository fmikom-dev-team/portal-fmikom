<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Model;

class JobCategory extends Model
{
    protected $table = 'job_categories';

    protected $fillable = ['nama', 'slug'];

    public function jobListings()
    {
        return $this->hasMany(JobListing::class);
    }
}
