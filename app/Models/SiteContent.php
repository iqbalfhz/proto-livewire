<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['group', 'key', 'value'])]
class SiteContent extends Model
{
    public static function get(string $group, string $key, string $default = ''): string
    {
        return static::where('group', $group)->where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $group, string $key, ?string $value): void
    {
        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $value]
        );
    }

    public static function group(string $group): array
    {
        return static::where('group', $group)
            ->pluck('value', 'key')
            ->toArray();
    }
}
