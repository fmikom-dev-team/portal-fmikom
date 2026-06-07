<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['nama', 'slug', 'deskripsi'];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    public function userModuleRoles(): HasMany
    {
        return $this->hasMany(UserModuleRole::class);
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'module_roles')
            ->withPivot('is_default')
            ->withTimestamps();
    }
}