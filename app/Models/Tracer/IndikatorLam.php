<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IndikatorLam extends Model
{
    use HasFactory;

    protected $table = 'indikator_lams';

    protected $fillable = [
        'kode_indikator',
        'nama_indikator',
        'kategori_lam',
        'tipe_data',
        'deskripsi'
    ];

 
    public function pertanyaans()
    {
        return $this->hasMany(Pertanyaan::class, 'indikator_id');
    }
}