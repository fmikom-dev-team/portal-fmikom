<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tracer\CareerHistory;

class Education extends Model
{
    use HasFactory;

    protected $table = 'education';

    protected $fillable = [
        'career_history_id',
        'nama_universitas',
        'program_studi_lanjutan',
        'jenjang_pendidikan',
        'sumber_biaya',
        'alamat_universitas',
    ];

    public function careerHistory()
    {
        return $this->belongsTo(CareerHistory::class, 'career_history_id');
    }
}
