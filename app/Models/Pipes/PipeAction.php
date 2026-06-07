<?php

namespace App\Models\Pipes;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipeAction extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'payload_mapping' => 'array',
    ];

    public function workflow()
    {
        return $this->belongsTo(PipeWorkflow::class, 'workflow_id');
    }

    public function provider()
    {
        return $this->belongsTo(PipeProvider::class, 'provider_id');
    }
}
