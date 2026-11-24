<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Support\UploadedImages;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'slug', 'description', 'role', 'year', 'problem', 'approach', 'outcome', 'tech_stack', 'image', 'demo_url', 'repo_url', 'is_featured', 'sort_order'])]
class Project extends Model
{
    use HasFactory, HasSlug;

    protected static function boot(): void
    {
        parent::boot();

        // Gallery rows go with the project through the foreign key, but a
        // cascading delete never reaches the files. Removing them one by one
        // beforehand lets each image's own hook sweep its file up.
        static::deleting(function (Project $project) {
            $project->images->each->delete();
        });

        static::deleted(fn (Project $project) => UploadedImages::delete($project->image));
    }

    /**
     * Screenshots shown beneath the project's main plate.
     *
     * @return HasMany<ProjectImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order')->orderBy('id');
    }

    protected function casts(): array
    {
        return [
            'tech_stack' => 'array',
            'is_featured' => 'boolean',
        ];
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }
}
