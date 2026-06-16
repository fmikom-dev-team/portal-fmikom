<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Bookmark extends Model
{
    protected $table = 'bookmarks';

    protected $fillable = ['user_id', 'job_id'];

    protected $casts = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobListing()
    {
        return $this->belongsTo(JobListing::class, 'job_id');
    }
}
