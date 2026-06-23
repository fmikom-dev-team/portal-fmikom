<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tracer\CareerHistory;

class Employment extends Model
{
    use HasFactory;

    protected $table = 'employment';

    protected $fillable = [
        'career_history_id',
        'nama_perusahaan',
        'jabatan',
        'sektor_industri',
        'alamat_perusahaan',
        'gaji_min',
        'gaji_max',
    ];

    protected $casts = [
        'gaji_min' => 'integer',
        'gaji_max' => 'integer',
    ];

    public function careerHistory()
    {
        return $this->belongsTo(CareerHistory::class, 'career_history_id');
    }
}
