<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    /**
     * Get setting value by key with optional default
     */
    public static function get($key, $default = null)
    {
        // Cache the settings query for performance or keep it simple
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}
