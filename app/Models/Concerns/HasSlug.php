<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Fill an empty slug from the title, never rewriting a published URL.
     */
    protected static function bootHasSlug(): void
    {
        static::saving(function (Model $model): void {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
        });
    }
}
