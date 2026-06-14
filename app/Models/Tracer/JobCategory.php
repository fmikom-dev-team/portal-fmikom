<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Model;

class JobCategory extends Model
{
    //
    protected $fillable = ['nama', 'slug'];

    public function jobListings()
    {
        return $this->hasMany(JobListing::class);
    }
}
