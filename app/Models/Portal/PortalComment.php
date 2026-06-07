<?php

namespace App\Models\Portal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalComment extends Model
{
    protected $guarded = [];

    public function post(): BelongsTo
    {
        return $this->belongsTo(PortalPost::class, 'post_id');
    }
}
