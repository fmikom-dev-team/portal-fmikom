<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class MitraProfiles extends Model
{
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function jobListings()
    {
        return $this->hasMany(JobListing::class, 'mitra_id');
    }
}
