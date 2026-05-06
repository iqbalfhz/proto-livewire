<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $stacks = [
            ['Laravel', 'Livewire', 'Tailwind CSS', 'MySQL'],
            ['Laravel', 'Vue.js', 'Tailwind CSS', 'PostgreSQL'],
            ['Laravel', 'Inertia.js', 'React', 'MySQL'],
            ['Laravel', 'Alpine.js', 'Bootstrap', 'SQLite'],
            ['PHP', 'MySQL', 'jQuery', 'Bootstrap'],
            ['Laravel', 'Livewire', 'Flux UI', 'MySQL'],
            ['Next.js', 'TypeScript', 'Tailwind CSS', 'PostgreSQL'],
            ['Laravel', 'REST API', 'Vue.js', 'Redis'],
        ];

        $title = fake()->words(fake()->numberBetween(2, 4), true);
        $title = ucwords($title);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->paragraph(2),
            'tech_stack' => fake()->randomElement($stacks),
            'image' => null,
            'demo_url' => fake()->boolean(60) ? fake()->url() : null,
            'repo_url' => fake()->boolean(70) ? 'https://github.com/'.fake()->userName().'/'.Str::slug($title) : null,
            'is_featured' => fake()->boolean(35),
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}
