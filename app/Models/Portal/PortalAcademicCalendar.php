<?php

namespace App\Models\Portal;

use Illuminate\Database\Eloquent\Model;

class PortalAcademicCalendar extends Model
{
    protected $table = 'portal_academic_calendars';
    
    protected $guarded = [];

    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];
}
