<?php

namespace App\Models\Tracer;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Tracer\EducationHistory;
use App\Models\Tracer\CareerHistory;
use App\Models\Tracer\Provinsi;
use App\Models\Tracer\Kota;


class ProfilAlumni extends Model
{
    use HasFactory;

    protected $with = ['user'];

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

    protected $hidden = ['nik', 'npwp'];

    protected $appends = ['completeness_percentage', 'nim', 'nama_lengkap', 'tahun_lulus', 'photo_path', 'program_studi'];

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

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class);
    }

    public function kota()
    {
        return $this->belongsTo(Kota::class);
    }

    public function getNimAttribute()
    {
        return $this->user?->nomor_induk;
    }

    public function getNamaLengkapAttribute()
    {
        return $this->user?->name;
    }

    public function getTahunLulusAttribute()
    {
        return $this->user?->tahun_lulus;
    }

    public function getPhotoPathAttribute()
    {
        return $this->user?->foto_path;
    }

    public function getProgramStudiAttribute()
    {
        return $this->user?->programStudi?->nama;
    }

    public function getCompletenessPercentageAttribute()
{
    $percentage = 0;

    // Data dari User (via relasi)
    $user = $this->relationLoaded('user') ? $this->user : $this->user()->first();
    
    if (!empty($user?->name)) $percentage += 10;
    if (!empty($user?->no_telepon)) $percentage += 10;

    // Data dari ProfilAlumni
    if (!empty($this->jenis_kelamin)) $percentage += 10;
    if (!empty($this->angkatan)) $percentage += 10;
    if (!empty($this->alamat_rumah)) $percentage += 10;
    if (!empty($this->provinsi_id) && !empty($this->kota_id)) $percentage += 10;

    // Karir (bobot besar karena ini inti tracer study)
    $hasCareers = $this->relationLoaded('careers')
        ? $this->careers->count() > 0
        : $this->careers()->exists();
    if ($hasCareers) $percentage += 40;

    return $percentage;
}
}
