<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $table = 'app_settings';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = ['key', 'value', 'group', 'label'];

    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public static function setValue(string $key, $value, $label = null, $group = null)
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'label' => $label, 'group' => $group]
        );
    }
}
