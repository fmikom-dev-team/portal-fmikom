<?php

namespace App\Models\Pipes;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipeProviderScope extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function provider()
    {
        return $this->belongsTo(PipeProvider::class, 'provider_id');
    }
}
