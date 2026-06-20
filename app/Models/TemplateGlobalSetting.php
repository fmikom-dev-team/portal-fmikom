<?php
// app/Models/TemplateGlobalSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TemplateGlobalSetting extends Model
{
    protected $fillable = ['key', 'value', 'tipe', 'label', 'deskripsi'];

    // ── Static helpers ────────────────────────────────────────────

    /**
     * Ambil value setting berdasarkan key.
     * Pakai cache 60 menit agar tidak query DB setiap render surat.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("tgs:{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting?->value ?? $default;
        });
    }

    /**
     * Set value dan clear cache.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("tgs:{$key}");
    }

    /**
     * Ambil semua setting sebagai key-value array.
     */
    public static function allAsArray(): array
    {
        return static::all()->pluck('value', 'key')->toArray();
    }

    /**
     * Clear semua cache setting.
     */
    public static function clearCache(): void
    {
        static::all()->each(fn ($s) => Cache::forget("tgs:{$s->key}"));
    }
}
