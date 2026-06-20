<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active',
        'logo_path',
    ];

    /**
     * Get the user roles associated with this module.
     */
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserModuleRole::class);
    }

    /**
     * Get the global roles allowed in this module.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'module_roles')
            ->withPivot('is_default')
            ->withTimestamps();
    }
}
