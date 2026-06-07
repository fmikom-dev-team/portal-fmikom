<?php

namespace App\Models\Pipes;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PipeProvider extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'supported_features' => 'array',
        'settings' => 'array',
    ];

    public function scopes()
    {
        return $this->hasMany(PipeProviderScope::class, 'provider_id');
    }

    public function connections()
    {
        return $this->hasMany(PipeConnection::class, 'provider_id');
    }

    public function healthIndicators()
    {
        return $this->hasMany(PipeProviderHealth::class, 'provider_id');
    }
}
