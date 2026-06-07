<?php

namespace App\Models\Alumni;

use App\Models\Kuesioner\ResponsKuesioner;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfilAlumni extends Model
{
    protected $fillable = [
        'user_id', 'program_studi_id', 'nim', 'tahun_lulus',
        'no_telepon', 'linkedin_url', 'foto_path',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class);
    }

    public function karirs(): HasMany
    {
        return $this->hasMany(KarirAlumni::class, 'alumni_id');
    }

    public function respons(): HasMany
    {
        return $this->hasMany(ResponsKuesioner::class, 'alumni_id');
    }

    public function getCurrentKarir(): ?KarirAlumni
    {
        return $this->karirs()->where('is_current', true)->first();
    }
}
