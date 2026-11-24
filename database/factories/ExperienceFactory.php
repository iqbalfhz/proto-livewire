<?php

namespace Database\Factories;

use App\Enums\ExperienceType;
use App\Models\Experience;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Experience>
 */
class ExperienceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => ExperienceType::Work,
            'title' => fake()->jobTitle(),
            'organization' => fake()->company(),
            'location' => fake()->city(),
            'started_on' => fake()->dateTimeBetween('-6 years', '-3 years'),
            'ended_on' => fake()->dateTimeBetween('-2 years', '-1 month'),
            'summary' => fake()->sentence(12),
        ];
    }

    public function current(): static
    {
        return $this->state(fn () => ['ended_on' => null]);
    }

    public function education(): static
    {
        return $this->state(fn () => ['type' => ExperienceType::Education]);
    }
}
