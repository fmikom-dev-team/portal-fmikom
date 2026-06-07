<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * AuthSetting — Key/Value store for all auth platform configuration.
 * Always go through get()/set() helpers to keep cache in sync.
 */
class AuthSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'description'];

    // ─── Typed Value Accessor ──────────────────────────────────────────────

    public function getTypedValue(): mixed
    {
        return match ($this->type) {
            'boolean' => (bool)(int) $this->value,
            'integer' => (int) $this->value,
            'json'    => json_decode($this->value, true),
            default   => $this->value,
        };
    }

    // ─── Static Helpers ───────────────────────────────────────────────────

    /**
     * Get a setting value by key, with caching.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("auth_setting.{$key}", 300, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->getTypedValue() : $default;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        $type = 'string';
        if (is_bool($value)) {
            $type = 'boolean';
            $valStr = $value ? '1' : '0';
        } elseif (is_int($value)) {
            $type = 'integer';
            $valStr = (string) $value;
        } elseif (is_array($value)) {
            $type = 'json';
            $valStr = json_encode($value);
        } else {
            $valStr = (string) $value;
        }

        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $valStr,
                'type'  => $type
            ]
        );

        // Invalidate cache
        Cache::forget("auth_setting.{$key}");
        Cache::forget('auth_settings.all');
    }

    /**
     * Get all settings as a flat key/value array (typed).
     */
    public static function getAllTyped(): array
    {
        return Cache::remember('auth_settings.all', 300, function () {
            return static::all()->mapWithKeys(
                fn($s) => [$s->key => $s->getTypedValue()]
            )->toArray();
        });
    }

    /**
     * Get all settings for a specific group.
     */
    public static function getGroup(string $group): array
    {
        return static::where('group', $group)->get()
            ->mapWithKeys(fn($s) => [$s->key => $s->getTypedValue()])
            ->toArray();
    }
}
