<?php

namespace App\Models\Tracer;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $table = 'events';

    protected $fillable = [
        'title',
        'description',
        'location',
        'event_date',
        'event_time_start',
        'event_time_end',
        'registration_deadline',
        'max_participants',
        'poster_path',
        'status',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'registration_deadline' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function registeredUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_registrations')
            ->withPivot('status', 'registered_at')
            ->withTimestamps();
    }
}
