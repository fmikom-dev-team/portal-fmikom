<?php

namespace App\Models\Auth;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property int $user_id
 * @property int $provider_id
 * @property string $external_id
 * @property string|null $email
 * @property string|null $access_token
 * @property string|null $refresh_token
 * @property Carbon|null $expires_at
 * @property AuthOAuthProvider|null $provider
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
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
