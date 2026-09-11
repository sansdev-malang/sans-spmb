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

    /**
     * Get Base64 encoded logo for DomPDF rendering.
     */
    public static function getLogoBase64(): ?string
    {
        $logoUrl = static::get('school_logo_url') ?: static::get('app_logo');
        if ($logoUrl) {
            $cleanPath = ltrim(str_replace('/storage/', '', $logoUrl), '/');
            $storagePath = storage_path('app/public/' . $cleanPath);
            if (file_exists($storagePath)) {
                $mime = mime_content_type($storagePath);
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($storagePath));
            }
            $publicPath = public_path(ltrim($logoUrl, '/'));
            if (file_exists($publicPath)) {
                $mime = mime_content_type($publicPath);
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($publicPath));
            }
        }

        $defaultPaths = [
            public_path('assets/images/logo.png'),
            public_path('logo/paud.png'),
        ];
        foreach ($defaultPaths as $dp) {
            if (file_exists($dp)) {
                $mime = mime_content_type($dp);
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($dp));
            }
        }

        return null;
    }
}
