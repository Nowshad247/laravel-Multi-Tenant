<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $table = 'settings';

    protected $fillable = ['key', 'value'];

    protected static array $cache = [];

    public static function getValue(string $key, $default = null)
    {
        $settings = self::allSettings();

        return $settings[$key] ?? $default;
    }

    public static function allSettings(): array
    {
        if (! empty(self::$cache)) {
            return self::$cache;
        }

        self::$cache = self::query()
            ->pluck('value', 'key')
            ->all();

        return self::$cache;
    }

    public static function clearStaticCache(): void
    {
        self::$cache = [];
    }
}
