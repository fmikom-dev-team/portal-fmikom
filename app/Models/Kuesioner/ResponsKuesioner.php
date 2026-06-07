<?php

namespace App\Models\Kuesioner;

use App\Models\Alumni\ProfilAlumni;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResponsKuesioner extends Model
{
    protected $fillable = [
        'kuesioner_id', 'alumni_id', 'is_complete', 'tanggal_isi',
    ];

    protected $casts = [
        'is_complete' => 'boolean',
        'tanggal_isi' => 'datetime',
    ];

    public function kuesioner(): BelongsTo
    {
        return $this->belongsTo(Kuesioner::class);
    }

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(ProfilAlumni::class, 'alumni_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailRespons::class, 'respons_id');
    }

    public function getProgress(): int
    {
        $totalPertanyaan = $this->kuesioner->pertanyaans()->count();
        if ($totalPertanyaan === 0) {
            return 100;
        }

        $answered = $this->details()->count();

        return (int) round(($answered / $totalPertanyaan) * 100);
    }
}
