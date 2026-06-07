<?php

namespace App\Models\Portal;

use Illuminate\Database\Eloquent\Model;

class PortalEvent extends Model
{
    protected $table = 'portal_events';

    protected $guarded = [];

    protected $casts = [
        'start_time' => 'datetime:Y-m-d H:i:s',
        'end_time' => 'datetime:Y-m-d H:i:s',
    ];
}
