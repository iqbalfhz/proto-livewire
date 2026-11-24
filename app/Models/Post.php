<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Support\HtmlSanitizer;
use App\Support\UploadedImages;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'slug', 'excerpt', 'tags', 'content', 'thumbnail', 'is_published', 'published_at'])]
class Post extends Model
{
    use HasFactory, HasSlug;

    protected static function boot(): void
    {
        parent::boot();

        static::deleted(function (Post $post) {
            UploadedImages::delete($post->thumbnail);

            foreach (UploadedImages::embeddedIn($post->content) as $path) {
                if (! static::whereKeyNot($post->getKey())->where('content', 'like', '%'.$path.'%')->exists()) {
                    UploadedImages::delete($path);
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /** Sanitised on the way in, because the body is rendered unescaped. */
    protected function content(): Attribute
    {
        return Attribute::set(fn (?string $value) => HtmlSanitizer::clean($value));
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->orderByDesc('published_at');
    }
}
