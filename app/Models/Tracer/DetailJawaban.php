<?php

namespace App\Models\Tracer;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailJawaban extends Model
{
    use HasFactory;

    protected $table = 'detail_jawabans';

    protected $fillable = [
        'response_id',
        'pertanyaan_id',
        'opsi_jawaban_id',
        'jawaban_text',
    ];

    /*
    |-------------------------
    | RELATIONSHIPS
    |-------------------------
    */

    // Detail jawaban milik 1 response
    public function response()
    {
        return $this->belongsTo(Response::class);
    }

    // Detail jawaban untuk 1 pertanyaan
    public function pertanyaan()
    {
        return $this->belongsTo(Pertanyaan::class);
    }

    // Opsional (kalau jawaban pakai pilihan)
    public function opsiJawaban()
    {
        return $this->belongsTo(OpsiJawaban::class);
    }
}
