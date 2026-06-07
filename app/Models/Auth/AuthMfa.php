<?php

namespace App\Models\Auth;

use App\Models\User;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuthMfa extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'auth_mfa';

    protected $fillable = [
        'user_id',
        'type',
        'secret',
        'is_active',
        'verified_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
