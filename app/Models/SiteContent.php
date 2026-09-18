<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable(['group', 'key', 'value'])]
class SiteContent extends Model
{
    /**
     * Bootstrap the model and its traits.
     *
     * Every page render reads at least one group, so the groups are cached and
     * flushed here — covering writes from the editors, seeders and tinker alike.
     */
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
            fn () => static::where('group', $group)->pluck('value', 'key')->toArray(),
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
