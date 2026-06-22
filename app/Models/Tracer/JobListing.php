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
        'deadline', 'is_salary_visible',
        'rejection_reason', 'rejected_at'
    ];

    protected $casts = [
        'salary_min' => 'integer',
        'salary_max' => 'integer',
        'is_salary_visible' => 'boolean',
        'deadline' => 'date',
        'rejected_at' => 'datetime',
    ];

    /*
    |-------------------------
    | SCOPES
    |-------------------------
    */

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mitra()
    {
        return $this->belongsTo(MitraProfile::class, 'mitra_id');
    }

    public function category()
    {
        return $this->belongsTo(JobCategory::class, 'job_category_id');
    }

    public function applicants()
    {
        return $this->hasMany(JobApplicant::class, 'job_id');
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class, 'job_id');
    }
}
