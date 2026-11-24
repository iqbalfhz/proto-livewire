<?php

namespace App\Models;

use App\Enums\ExperienceType;
use Database\Factories\ExperienceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['type', 'title', 'organization', 'location', 'started_on', 'ended_on', 'summary'])]
class Experience extends Model
{
    /** @use HasFactory<ExperienceFactory> */
    use HasFactory;

    /**
     * Current positions first, then most recently ended.
     *
     * @param  Builder<Experience>  $query
     * @return Builder<Experience>
     */
    public function scopeChronological(Builder $query): Builder
    {
        return $query->orderByRaw('ended_on IS NULL DESC')->orderByDesc('ended_on')->orderByDesc('started_on');
    }

    public function period(): string
    {
        return $this->started_on->format('M Y').' — '.($this->ended_on?->format('M Y') ?? 'Present');
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array
    {
        return [
            'type' => ExperienceType::class,
            'started_on' => 'date',
            'ended_on' => 'date',
        ];
    }
}
