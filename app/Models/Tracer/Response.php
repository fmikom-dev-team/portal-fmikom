<?php

namespace App\Models\Tracer;

use App\Models\User;
use App\Modules\Trace\Services\TraceCacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function ($response) {
            TraceCacheService::forgetQuestionnaireCaches($response->kuesioner_id);
            TraceCacheService::forgetDashboardCaches(userId: $response->user_id);
        });

        static::deleted(function ($response) {
            TraceCacheService::forgetQuestionnaireCaches($response->kuesioner_id);
            TraceCacheService::forgetDashboardCaches(userId: $response->user_id);
        });
    }

    protected $table = 'responses';

    protected $fillable = [
        'user_id',
        'kuesioner_id',
        'submitted_at',
        'angkatan',
        'stakeholder_name',
        'stakeholder_email',
    ];

    protected $hidden = ['stakeholder_email'];

    /*
    |-------------------------
    | CASTS
    |-------------------------
    */
    protected $casts = [
        'submitted_at' => 'datetime',
        'angkatan' => 'integer',
    ];

    /*
    |-------------------------
    | RELATIONSHIPS
    |-------------------------
    */

    // Response dimiliki user (alumni)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Response untuk 1 kuesioner
    public function kuesioner()
    {
        return $this->belongsTo(Kuesioner::class);
    }

    // Response punya banyak detail jawaban
    public function detailJawabans()
    {
        return $this->hasMany(DetailJawaban::class);
    }
}
