<?php

namespace App\Models\Tracer;

use App\Models\User;
use App\Modules\Trace\Services\TraceCacheService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kuesioner extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected static function booted(): void
    {
        static::saved(function ($kuesioner) {
            TraceCacheService::forgetQuestionnaireCaches($kuesioner->id);
        });

        static::deleted(function ($kuesioner) {
            TraceCacheService::forgetQuestionnaireCaches($kuesioner->id);
        });
    }

    protected $table = 'kuesioner';

    protected $fillable = [
        'judul',
        'subtitle',
        'kategori',
        'tahun',
        'date_mulai',
        'date_selesai',
        'deskripsi',
        'is_active',
        'status',
        'tipe_kuesioner',
        'created_by',
    ];

    /*
    |-------------------------
    | CASTS
    |-------------------------
    */
    protected $casts = [
        'date_mulai' => 'date',
        'date_selesai' => 'date',
        'tahun' => 'integer',
        'is_active' => 'boolean',
    ];

    /*
    |-------------------------
    | RELATIONSHIPS
    |-------------------------
    */

    /*
    |-------------------------
    | SCOPES
    |-------------------------
    */

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'published']);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function responses()
    {
        return $this->hasMany(Response::class, 'kuesioner_id');
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}
