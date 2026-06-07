<?php

namespace App\Models\Auth;

use App\Models\User;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthMagicLink extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'auth_magic_links';

    protected $fillable = [
        'user_id', 'email', 'token', 'token_hash',
        'is_used', 'used_at', 'expires_at',
        'ip_address', 'user_agent',
    ];

    protected $casts = [
        'is_used'    => 'boolean',
        'used_at'    => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return !$this->is_used && !$this->isExpired();
    }
}
