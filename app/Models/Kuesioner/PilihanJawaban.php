<?php

namespace App\Models\Kuesioner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PilihanJawaban extends Model
{
    protected $fillable = [
        'pertanyaan_id', 'teks_pilihan', 'nilai'
    ];

    public function pertanyaan(): BelongsTo
    {
        return $this->belongsTo(PertanyaanKuesioner::class, 'pertanyaan_id');
    }

    public function detailRespons(): HasMany
    {
        return $this->hasMany(DetailRespons::class, 'pilihan_id');
    }
}