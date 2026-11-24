<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProjectImage>
 */
class ProjectImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'path' => 'projects/gallery/'.Str::random(40).'.jpg',
            'caption' => fake()->boolean(70) ? ucfirst(fake()->words(3, true)) : null,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}
