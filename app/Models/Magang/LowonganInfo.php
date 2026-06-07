<?php

namespace App\Models\Magang;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LowonganInfo extends Model
{
    protected $fillable = [
        'pembuat_id', 'judul', 'deskripsi', 'tipe', 'nama_perusahaan',
        'tanggal_mulai', 'tanggal_selesai', 'poster_path', 'link_eksternal'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pembuat_id');
    }

    public function isAktif(): bool
    {
        $now = now();
        return $now->between($this->tanggal_mulai, $this->tanggal_selesai);
    }

    public function scopeAktif(Builder $query): Builder
    {
        $now = now();
        return $query->where('tanggal_mulai', '<=', $now)
                   ->where('tanggal_selesai', '>=', $now);
    }
}