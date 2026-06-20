<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratLampiran extends Model
{
    protected $fillable = [
        'surat_id',
        'nama_file',
        'file_path',
        'tipe',
    ];

    /**
     * @return BelongsTo<Surat, $this>
     */
    public function surat(): BelongsTo
    {
        return $this->belongsTo(Surat::class);
    }
}
