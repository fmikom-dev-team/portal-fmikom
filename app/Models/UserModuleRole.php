<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserModuleRole extends Model
{
    protected $table = 'user_module_roles';

    protected $fillable = [
        'user_id',
        'module_id',
        'role_id',
        'is_active',
    ];

    /**
     * Get the user that owns the module role.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the module associated with the role.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the role associated with the module.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
