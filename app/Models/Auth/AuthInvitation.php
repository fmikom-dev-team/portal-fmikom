<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthInvitation extends Model
{
    use HasFactory;

    protected $table = 'auth_invitations';

    protected $fillable = [
        'module_id',
        'email',
        'role',
        'status',
        'token',
        'invited_by',
        'organization_name',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
}
