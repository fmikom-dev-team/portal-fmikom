<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

class SuratLampiran extends Model
{
    protected $fillable = [
        'surat_id',
        'nama_file',
        'file_path',
        'tipe',
    ];

    public function getUrlAttribute(): string
    {
        if ($this->exists && $this->getKey()) {
            return URL::temporarySignedRoute(
                'documents.public.lampiran.preview',
                now()->addMinutes(15),
                ['id' => $this->getKey()],
            );
        }

        return '';
    }

    /**
     * @return BelongsTo<Surat, $this>
     */
    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }
}
