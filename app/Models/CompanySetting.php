<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, string $default = ''): string
    {
        return static::where('key', $key)->value('value') ?? $default;
    }

    public static function all_settings(): array
    {
        return static::all()->pluck('value', 'key')->toArray();
    }
}
