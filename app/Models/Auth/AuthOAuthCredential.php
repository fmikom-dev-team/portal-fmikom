<?php

namespace App\Models\Auth;

use App\Models\User;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthOAuthCredential extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'auth_oauth_credentials';

    protected $fillable = [
        'user_id',
        'provider_id',
        'external_id',
        'email',
        'access_token',
        'refresh_token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provider()
    {
        return $this->belongsTo(AuthOAuthProvider::class, 'provider_id');
    }
}
