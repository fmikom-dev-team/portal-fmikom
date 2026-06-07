<?php

namespace App\Models\Portal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PortalSetting extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saved(fn () => Cache::forget('portal_settings'));
        static::deleted(fn () => Cache::forget('portal_settings'));
    }

    protected $fillable = ['key', 'value'];
}
