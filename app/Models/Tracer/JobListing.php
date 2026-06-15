<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
use App\Models\User;


class JobListing extends Model
{
    use SoftDeletes;

    protected $table = 'jobs_listings';

    protected $fillable = [
        'user_id',
        'mitra_id', 'job_category_id', 'title', 'description', 
        'experience_level', 'location_type', 'location_city', 
        'tipe_kerja', 'salary_min', 'salary_max', 'status', 
        'deadline', 'is_salary_visible'
    ];

    protected $casts = [
        'salary_min' => 'integer',
        'salary_max' => 'integer',
        'is_salary_visible' => 'boolean',
        'deadline' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mitra()
    {
        return $this->belongsTo(MitraProfiles::class, 'mitra_id');
    }

    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'job_category_id');
    }

    public function applicants()
    {
        return $this->hasMany(JobApplycants::class, 'job_id');
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmarks::class, 'job_id');
    }
}
