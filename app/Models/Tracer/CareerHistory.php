<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tracer\ProfilAlumni;
use App\Models\Tracer\Provinsi;
use App\Models\Tracer\Kota;
use App\Models\Tracer\Employment;
use App\Models\Tracer\Education;
use App\Enums\CareerStatus;
use App\Enums\CareerType;

class CareerHistory extends Model
{
    use HasFactory;

    protected $table = 'career_history';

    protected $fillable = [
        'profil_alumni_id',
        'type',
        'status',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_current',
        'provinsi_id',
        'kota_id',
        'latitude',
        'longitude',
        'tahun_mulai',
        'tahun_selesai',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'provinsi_id' => 'integer',
        'kota_id' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'status' => CareerStatus::class,
        'type' => CareerType::class,
    ];

    public function alumniProfile()
    {
        return $this->belongsTo(ProfilAlumni::class, 'profil_alumni_id');
    }

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class);
    }

    public function kota()
    {
        return $this->belongsTo(Kota::class);
    }

    public function employment()
    {
        return $this->hasOne(Employment::class, 'career_history_id');
    }

    public function education()
    {
        return $this->hasOne(Education::class, 'career_history_id');
    }
}
