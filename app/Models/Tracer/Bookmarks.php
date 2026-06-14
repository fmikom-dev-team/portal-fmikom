<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Bookmarks extends Model
{
    //
    protected $fillable = ['user_id', 'job_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobListing()
    {
        return $this->belongsTo(JobListing::class, 'job_id');
    }
}
