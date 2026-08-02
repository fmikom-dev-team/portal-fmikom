<?php

namespace App\Models\Auth;

use App\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Auth\AuthSession
 *
 * @property string $id
 * @property int $user_id
 * @property string|null $device_id
 * @property string $session_token
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array|null $geolocation
 * @property bool $is_revoked
 * @property int $risk_score
 * @property \Carbon\CarbonImmutable|null $expires_at
 * @property \Carbon\CarbonImmutable|null $last_activity_at
 * @property \Carbon\CarbonImmutable|null $created_at
 * @property \Carbon\CarbonImmutable|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AuthSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthSession query()
 * @method static \Illuminate\Database\Eloquent\Builder|AuthSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthSession whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AuthSession whereIsRevoked($value)
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class AuthSession extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'device_id',
        'session_token',
        'ip_address',
        'user_agent',
        'geolocation',
        'is_revoked',
        'risk_score',
        'expires_at',
        'last_activity_at',
        // Timestamps must be in $fillable so SessionEngine's explicit UTC strings
        // are respected. Without this, Eloquent's freshTimestamp() overrides with
        // app.timezone (Asia/Jakarta), causing a +7h offset that breaks SecureSession's
        // UTC-based expiry and idle timeout calculations.
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'geolocation' => 'array',
        'is_revoked' => 'boolean',
        'expires_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(AuthDevice::class, 'device_id');
    }
}
