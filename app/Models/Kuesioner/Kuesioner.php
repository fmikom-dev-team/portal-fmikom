<?php

namespace App\Models\Kuesioner;
use App\Models\Alumni\ProfilAlumni;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kuesioner extends Model
{
    protected $fillable = [
        'pembuat_id', 'judul', 'deskripsi', 'periode_mulai',
        'periode_selesai', 'status', 'tujuan'
    ];

    protected $casts = [
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
    ];

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_id');
    }

    public function pertanyaans(): HasMany
    {
        return $this->hasMany(PertanyaanKuesioner::class);
    }

    public function respons(): HasMany
    {
        return $this->hasMany(ResponsKuesioner::class);
    }

    public function isAktif(): bool
    {
        $now = now();
        return $this->status === 'published' && $now->between($this->periode_mulai, $this->periode_selesai);
    }

    public function getPersentaseRespons(): float
    {
        $totalAlumni = ProfilAlumni::count();
        if ($totalAlumni === 0) return 0;
        $responded = $this->respons()->where('is_complete', true)->count();
        return ($responded / $totalAlumni) * 100;
    }
}