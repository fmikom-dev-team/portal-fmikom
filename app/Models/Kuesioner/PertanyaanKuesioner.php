<?php

namespace App\Models\Kuesioner;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PertanyaanKuesioner extends Model
{
    protected $fillable = [
        'kuesioner_id', 'teks_pertanyaan', 'tipe', 'urutan', 'wajib_diisi',
    ];

    protected $casts = [
        'wajib_diisi' => 'boolean',
    ];

    public function kuesioner(): BelongsTo
    {
        return $this->belongsTo(Kuesioner::class);
    }

    public function pilihanJawabans(): HasMany
    {
        return $this->hasMany(PilihanJawaban::class, 'pertanyaan_id');
    }

    public function detailRespons(): HasMany
    {
        return $this->hasMany(DetailRespons::class, 'pertanyaan_id');
    }
}
