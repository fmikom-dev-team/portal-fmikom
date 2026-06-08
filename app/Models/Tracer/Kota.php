<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tracer\Provinsi;

class Kota extends Model
{
    use HasFactory;

    protected $table = 'kota';

    protected $fillable = [
        'provinsi_id',
        'name',
        'latitude',
        'longitude',
    ];

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class);
    }
}