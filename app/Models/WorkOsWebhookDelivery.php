<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOsWebhookDelivery extends Model
{
    use HasFactory;

    protected $table = 'work_os_webhook_deliveries';

    protected $fillable = [
        'webhook_id',
        'event_type',
        'payload',
        'response_status',
        'response_body',
        'latency_ms',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function webhook()
    {
        return $this->belongsTo(WorkOsWebhook::class, 'webhook_id');
    }
}
