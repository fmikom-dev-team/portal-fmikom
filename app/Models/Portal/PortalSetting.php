<?php

namespace App\Models\Portal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PortalSetting extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saved(fn () => \Illuminate\Support\Facades\Cache::forget('portal_settings'));
        static::deleted(fn () => \Illuminate\Support\Facades\Cache::forget('portal_settings'));
    }

    protected $fillable = ['key', 'value'];
}
