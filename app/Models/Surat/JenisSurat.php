<?php

namespace App\Models\Surat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisSurat extends Model
{
    protected $fillable = ['nama', 'deskripsi', 'template_file_path'];

    public function surats(): HasMany
    {
        return $this->hasMany(Surat::class);
    }
}
