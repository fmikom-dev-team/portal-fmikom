<?php

namespace App\Models\Kuesioner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailRespons extends Model
{
    protected $fillable = [
        'respons_id', 'pertanyaan_id', 'pilihan_id', 'jawaban_text'
    ];

    public function respons(): BelongsTo
    {
        return $this->belongsTo(ResponsKuesioner::class, 'respons_id');
    }

    public function pertanyaan(): BelongsTo
    {
        return $this->belongsTo(PertanyaanKuesioner::class, 'pertanyaan_id');
    }

    public function pilihanJawaban(): BelongsTo
    {
        return $this->belongsTo(PilihanJawaban::class, 'pilihan_id');
    }
}