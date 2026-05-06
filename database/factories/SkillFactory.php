<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Skill>
 */
class SkillFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $skills = [
            ['name' => 'Laravel',         'category' => 'Backend'],
            ['name' => 'PHP',             'category' => 'Backend'],
            ['name' => 'MySQL',           'category' => 'Database'],
            ['name' => 'PostgreSQL',      'category' => 'Database'],
            ['name' => 'Livewire',        'category' => 'Frontend'],
            ['name' => 'Alpine.js',       'category' => 'Frontend'],
            ['name' => 'Vue.js',          'category' => 'Frontend'],
            ['name' => 'React',           'category' => 'Frontend'],
            ['name' => 'Tailwind CSS',    'category' => 'Frontend'],
            ['name' => 'JavaScript',      'category' => 'Frontend'],
            ['name' => 'TypeScript',      'category' => 'Frontend'],
            ['name' => 'Git',             'category' => 'Tools'],
            ['name' => 'Docker',          'category' => 'Tools'],
            ['name' => 'Linux',           'category' => 'Tools'],
            ['name' => 'REST API',        'category' => 'Backend'],
            ['name' => 'Redis',           'category' => 'Database'],
        ];

        $skill = fake()->unique()->randomElement($skills);

        return [
            'name' => $skill['name'],
            'icon' => null,
            'category' => $skill['category'],
            'level' => fake()->numberBetween(60, 95),
            'sort_order' => fake()->numberBetween(1, 100),
        ];
    }
}
