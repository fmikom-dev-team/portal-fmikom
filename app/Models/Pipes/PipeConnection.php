<?php

namespace App\Models\Pipes;

use App\Models\Module;
use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PipeConnection extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'last_sync_at' => 'datetime',
        'granted_scopes' => 'array',
        'settings' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Module::class, 'organization_id');
    }

    public function provider()
    {
        return $this->belongsTo(PipeProvider::class, 'provider_id');
    }

    public function tokens()
    {
        return $this->hasMany(PipeConnectionToken::class, 'connection_id');
    }

    public function syncLogs()
    {
        return $this->hasMany(PipeSyncLog::class, 'connection_id');
    }

    public function webhooks()
    {
        return $this->hasMany(PipeWebhook::class, 'connection_id');
    }

    // Helper to get the active token
    public function getActiveToken()
    {
        return $this->tokens()->where('expires_at', '>', now())->latest()->first()
            ?? $this->tokens()->latest()->first();
    }
}
