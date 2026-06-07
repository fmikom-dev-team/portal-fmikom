<?php

namespace App\Models\Pipes;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipeProviderHealth extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'pipe_provider_health';

    protected $guarded = [];

    protected $casts = [
        'last_checked_at' => 'datetime',
        'error_rate_percent' => 'decimal:2',
    ];

    public function provider()
    {
        return $this->belongsTo(PipeProvider::class, 'provider_id');
    }
}
