<?php

namespace App\Models\Tracer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Tracer\EducationHistory;
use App\Models\Tracer\CareerHistory;


class ProfilAlumni extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'angkatan',
        'alamat_rumah',
        'latitude_rumah',
        'longitude_rumah',
        'jenis_kelamin',
        'nik',
        'npwp',
        'provinsi_id',
        'kota_id',
    ];

    protected $appends = ['completeness_percentage'];

     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function careers()
    {
        return $this->hasMany(CareerHistory::class, 'profil_alumni_id');
    }

    public function educationHistories()
    {
        return $this->hasMany(EducationHistory::class, 'profil_alumni_id');
    }

    public function getCompletenessPercentageAttribute()
    {
        $percentage = 0;
        
        if (!empty($this->nama_lengkap)) $percentage += 10;
        if (!empty($this->no_hp)) $percentage += 10;
        if (!empty($this->jenis_kelamin)) $percentage += 10;

        if (!empty($this->alamat_rumah)) $percentage += 10;
        if (!empty($this->provinsi_id) && !empty($this->kota_id)) $percentage += 10;
        if (!empty($this->latitude_rumah) && !empty($this->longitude_rumah)) $percentage += 10;

        $hasCareers = $this->relationLoaded('careers') ? $this->careers->count() > 0 : $this->careers()->exists();
        if ($hasCareers) {
            $percentage += 40;
        }

        return $percentage;
    }
}
