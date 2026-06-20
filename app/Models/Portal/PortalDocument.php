<?php

namespace App\Models\Portal;

use Illuminate\Database\Eloquent\Model;

class PortalDocument extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_pinned' => 'boolean',
        'download_count' => 'integer',
        'file_size' => 'integer',
    ];
}
