<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuratCategory extends Model
{
    protected $fillable = [
        'nama',
        'slug',
        'deskripsi',
        'urutan',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<JenisSurat, $this>
     */
    public function jenisSurats(): HasMany
    {
        return $this->hasMany(JenisSurat::class, 'category_id');
    }
}
