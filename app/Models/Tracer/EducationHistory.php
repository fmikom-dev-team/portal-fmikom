<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationHistory extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database (opsional jika nama tabel sudah jamak/plural)
     */
    protected $table = 'education_histories';

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'profil_alumni_id',
        'perguruan_tinggi',
        'program_studi',
        'jenjang',
        'sumber_biaya',
        'alamat',
        'latitude',
        'longitude',
        'tahun_mulai',
        'tahun_lulus',
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Relasi balik ke Profil Alumni
     */
    public function alumniProfile(): BelongsTo
    {
        return $this->belongsTo(ProfilAlumni::class, 'profil_alumni_id');
    }
}
