<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuthOAuthProvider extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'auth_oauth_providers';

    protected $fillable = [
        'name',
        'slug',
        'is_enabled',
        'client_id',
        'client_secret',
        'scopes',
        'use_demo_credentials',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'use_demo_credentials' => 'boolean',
        'scopes' => 'array',
    ];
}
