<?php

namespace App\Models\Pipes;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipeSyncLog extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'sync_checkpoint' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function connection()
    {
        return $this->belongsTo(PipeConnection::class, 'connection_id');
    }
}
