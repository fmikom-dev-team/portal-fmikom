<?php

namespace App\Models\Pipes;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class PipeWebhook extends Model
{
    use HasFactory, HasUuids;

    protected $guarded = [];

    protected $casts = [
        'events' => 'array',
    ];

    public function setSigningSecretAttribute($value)
    {
        if ($value) {
            $this->attributes['signing_secret'] = Crypt::encryptString($value);
        }
    }

    public function getSigningSecretAttribute($value)
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function connection()
    {
        return $this->belongsTo(PipeConnection::class, 'connection_id');
    }
}
