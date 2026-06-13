<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogbookPhoto extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'logbook_id',
        'file_path',
    ];

    public function logbook(): BelongsTo
    {
        return $this->belongsTo(LogbookMagang::class, 'logbook_id');
    }
}
