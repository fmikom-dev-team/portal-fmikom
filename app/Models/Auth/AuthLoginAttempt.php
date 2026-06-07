<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthLoginAttempt extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'email',
        'ip_address',
        'is_successful',
        'provider',
        'failure_reason',
        'risk_score',
    ];

    protected $casts = [
        'is_successful' => 'boolean',
    ];
}
