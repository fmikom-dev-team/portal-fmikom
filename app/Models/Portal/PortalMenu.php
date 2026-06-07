<?php

namespace App\Models\Portal;

use Illuminate\Database\Eloquent\Model;

class PortalMenu extends Model
{
    protected static function booted()
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('portal_menus'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('portal_menus'));
    }

    protected $guarded = [];

    public function page()
    {
        return $this->belongsTo(PortalPage::class, 'portal_page_id');
    }

    public function children()
    {
        return $this->hasMany(PortalMenu::class, 'parent_id')->orderBy('order');
    }

    public function parent()
    {
        return $this->belongsTo(PortalMenu::class, 'parent_id');
    }
}
