<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Posts: 5 published, 2 draft
        Post::factory(5)->published()->create();
        Post::factory(2)->create();

        // Projects: 4 featured + 6 regular
        Project::factory(4)->featured()->create();
        Project::factory(6)->create();

        // Skills: 10 items (unique via factory)
        Skill::factory(10)->create();
    }
}
