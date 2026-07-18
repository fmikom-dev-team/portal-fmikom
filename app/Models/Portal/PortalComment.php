<?php

namespace App\Models\Portal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortalComment extends Model
{
    protected $fillable = [
        'post_id',
        'author_name',
        'author_email',
        'content',
        'status',
        'ip_address',
        'user_agent',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(PortalPost::class, 'post_id');
    }
}
