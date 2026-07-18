<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOsWebhook extends Model
{
    use HasFactory;

    protected $table = 'work_os_webhooks';

    protected $fillable = [
        'url',
        'secret',
        'events',
        'is_active',
    ];

    protected $casts = [
        'events' => 'array',
        'is_active' => 'boolean',
    ];
}
