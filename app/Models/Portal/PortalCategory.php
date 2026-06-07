<?php

namespace App\Models\Portal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PortalCategory extends Model
{
    protected $guarded = [];

    public function posts(): HasMany
    {
        return $this->hasMany(PortalPost::class, 'category_id');
    }
}
