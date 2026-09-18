<?php

namespace App\Models;

use App\Support\UploadedImages;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['title', 'slug', 'excerpt', 'content', 'thumbnail', 'is_published', 'published_at'])]
class Post extends Model
{
    use HasFactory;

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Post $post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });

        // Never regenerate an existing slug from the title: the post is already
        // published under that URL. Only fill a slug that was emptied out.
        static::updating(function (Post $post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });

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
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->orderByDesc('published_at');
    }
}
