<?php

namespace App\Models\Surat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratLampiran extends Model
{
    protected $fillable = ['surat_id', 'nama_file', 'file_path', 'tipe'];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }

    public function getUrlAttribute(): string
    {
        if ($this->exists && $this->getKey()) {
            return route('documents.lampiran.preview', $this->getKey(), absolute: false);
        }

        return '';
    }
}
