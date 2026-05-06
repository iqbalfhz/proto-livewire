<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(fake()->numberBetween(4, 8));
        $publishedAt = fake()->boolean(75)
            ? fake()->dateTimeBetween('-1 year', 'now')
            : null;

        return [
            'title' => rtrim($title, '.'),
            'slug' => Str::slug(rtrim($title, '.')),
            'excerpt' => fake()->paragraph(2),
            'content' => implode("\n\n", fake()->paragraphs(fake()->numberBetween(4, 8))),
            'thumbnail' => null,
            'is_published' => $publishedAt !== null,
            'published_at' => $publishedAt,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'is_published' => true,
            'published_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ]);
    }
}
