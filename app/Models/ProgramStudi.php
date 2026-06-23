<?php

namespace App\Models;

use App\Models\Tracer\ProfilAlumni;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramStudi extends Model
{
    protected $fillable = ['fakultas_id', 'nama', 'kode'];

    public function fakultas(): BelongsTo
    {
        return $this->belongsTo(Fakultas::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function profilAlumnis(): HasMany
    {
        return $this->hasMany(ProfilAlumni::class);
    }
}
