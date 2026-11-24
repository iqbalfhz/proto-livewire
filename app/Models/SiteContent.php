<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['group', 'key', 'value'])]
class SiteContent extends Model
{
    /** Groups are cached for every render and flushed on any write. */
    protected static function boot(): void
    {
        parent::boot();

        static::saved(fn (SiteContent $content) => static::flush($content->group));
        static::deleted(fn (SiteContent $content) => static::flush($content->group));
    }

    public static function get(string $group, string $key, string $default = ''): string
    {
        return static::group($group)[$key] ?? $default;
    }

    public static function set(string $group, string $key, ?string $value): void
    {
        static::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $value]
        );
    }

    /**
     * @return array<string, string|null>
     */
    public static function group(string $group): array
    {
        return Cache::rememberForever(
            static::cacheKey($group),
            // Blank fields are dropped so every view falls back to its default.
            fn () => static::where('group', $group)->pluck('value', 'key')->filter(fn (?string $value) => filled($value))->toArray(),
        );
    }

    public static function flush(string $group): void
    {
        Cache::forget(static::cacheKey($group));
    }

    protected static function cacheKey(string $group): string
    {
        return "site_content.{$group}";
    }
}
