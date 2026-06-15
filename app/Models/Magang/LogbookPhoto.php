<?php

namespace App\Models\Magang;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogbookPhoto extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'logbook_photos';

    protected $fillable = [
        'logbook_id',
        'file_path',
    ];

    public function logbook(): BelongsTo
    {
        return $this->belongsTo(LogbookMagang::class, 'logbook_id');
    }
}
