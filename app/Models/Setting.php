<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $guarded = [];

    protected static $runtimeCache = null;

    /**
     * Get a setting by key.
     */
    public static function get($key, $default = null)
    {
        if (static::$runtimeCache === null) {
            try {
                static::$runtimeCache = \Illuminate\Support\Facades\Cache::remember('spmb_app_settings_map', 3600, function() {
                    return self::pluck('value', 'key')->toArray();
                });
            } catch (\Throwable $e) {
                try {
                    static::$runtimeCache = self::pluck('value', 'key')->toArray();
                } catch (\Throwable $ex) {
                    static::$runtimeCache = [];
                }
            }
        }

        if (array_key_exists($key, static::$runtimeCache)) {
            return static::$runtimeCache[$key] !== null ? static::$runtimeCache[$key] : $default;
        }

        return $default;
    }

    /**
     * Set a setting by key.
     */
    public static function set($key, $value)
    {
        try {
            \Illuminate\Support\Facades\Cache::forget('spmb_app_settings_map');
        } catch (\Throwable $e) {}

        if (static::$runtimeCache !== null) {
            static::$runtimeCache[$key] = $value;
        }

        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
