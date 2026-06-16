<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class MitraProfile extends Model
{
    protected $table = 'mitra_profiles';

    protected $fillable = [
        'user_id',
        'nama_perusahaan',
        'deskripsi',
        'website',
        'logo_path',
        'email_perusahaan',
        'no_telp',
        'alamat_lengkap',
        'provinsi_id',
        'kota_id'
    ];
    protected $appends = ['logo_url'];

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) return null;
        // Handle both formats: with /storage/ prefix (legacy) and without (current)
        if (str_starts_with($this->logo_path, '/storage/')) {
            return $this->logo_path;
        }
        if (str_starts_with($this->logo_path, 'http')) {
            return $this->logo_path;
        }
        return '/storage/' . $this->logo_path;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobListings()
    {
        return $this->hasMany(JobListing::class, 'mitra_id');
    }
}
