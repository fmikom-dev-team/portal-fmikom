<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpsiJawaban extends Model
{
    use HasFactory;

    protected $table = 'opsi_jawabans';

    protected $fillable = [
        'pertanyaan_id',
        'label',
        'nilai',
        'urutan',
    ];

    /*
    |-------------------------
    | CASTS
    |-------------------------
    */
    protected $casts = [
        'nilai' => 'integer',
        'urutan' => 'integer',
    ];

    /*
    |-------------------------
    | RELATIONSHIPS
    |-------------------------
    */

    public function pertanyaan()
    {
        return $this->belongsTo(Pertanyaan::class);
    }
}
