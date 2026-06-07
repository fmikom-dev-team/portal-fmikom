<?php

namespace App\Models\Pipes;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipeMonitoringMetric extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false; // Using custom timestamp
    protected $guarded = [];

    protected $casts = [
        'tags' => 'array',
        'timestamp' => 'datetime',
        'value' => 'decimal:4',
    ];

    public function provider()
    {
        return $this->belongsTo(PipeProvider::class, 'provider_id');
    }

    public function connection()
    {
        return $this->belongsTo(PipeConnection::class, 'connection_id');
    }
}
