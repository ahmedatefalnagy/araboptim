<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get($key, $default = null)
    {
        $value = static::where('key', $key)->value('value');

        // إرجاع القيمة الافتراضية إذا كان المفتاح غير موجود أو قيمته فارغة،
        // حتى لا تظهر حقول فارغة في المستندات (مثل اسم الشركة في الـ PDF).
        return ($value === null || $value === '') ? $default : $value;
    }

    public static function set($key, $value)
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}