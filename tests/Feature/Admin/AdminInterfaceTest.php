<?php

use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;

beforeEach(fn () => $this->actingAs(User::factory()->admin()->create()));

test('the dashboard summarises content as plain figures', function () {
    Post::factory()->count(2)->create(['is_published' => true, 'published_at' => now()]);
    Post::factory()->create(['is_published' => false, 'published_at' => null]);
    Project::factory()->featured()->create();
    ContactMessage::create(['name' => 'Rina', 'email' => 'rina@example.com', 'message' => 'Halo']);

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSeeInOrder(['Posts', '3', '2 published', 'Projects', '1', '1 featured', 'Messages', '1', '1 unread'])
        ->assertSee('Rina');
});

test('icon-only actions carry accessible names', function (string $route, array $labels) {
    Post::factory()->create();
    Project::factory()->create();
    Skill::factory()->create();
    ContactMessage::create(['name' => 'Rina', 'email' => 'rina@example.com', 'message' => 'Halo']);

    $response = $this->get(route($route))->assertOk();

    foreach ($labels as $label) {
        $response->assertSee('aria-label="'.$label.'"', false);
    }
})->with([
    'posts' => ['admin.blog.index', ['Edit post', 'Delete post']],
    'projects' => ['admin.projects.index', ['Edit project', 'Delete project']],
    'skills' => ['admin.skills.index', ['Edit skill', 'Delete skill']],
    'messages' => ['admin.messages.index', ['View message', 'Mark as read', 'Delete message']],
    'back button' => ['admin.about', ['Back', 'Profile photo']],
    'post form' => ['admin.blog.create', ['Thumbnail']],
]);

test('the brand is a plain link, not the height-capped Flux logo slot', function () {
    $this->get(route('admin.dashboard'))
        ->assertSee('data-admin-brand', false)
        ->assertDontSee('data-flux-brand', false);
});

test('the layout exposes a main landmark and named navigation', function () {
    $this->get(route('admin.dashboard'))
        ->assertSee('role="main"', false)
        ->assertSee('aria-label="Admin"', false)
        ->assertSee('aria-label="Site"', false);
});

test('the project form names its uploads and labels the featured toggle', function () {
    $this->get(route('admin.projects.edit', Project::factory()->create()))
        ->assertSee('aria-label="Project image"', false)
        ->assertSee('aria-label="Add screenshots"', false)
        ->assertSee('Featured project');
});

test('the pagination footer only appears when there is a second page', function () {
    Post::factory()->create();
    $this->get(route('admin.blog.index'))->assertDontSee('admin-panel-footer', false);

    Post::factory()->count(15)->create();
    $this->get(route('admin.blog.index'))->assertSee('admin-panel-footer', false);
});
