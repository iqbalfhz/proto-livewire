<?php

use App\Models\Post;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Support\Facades\Schema;

test('skills no longer store a self-rated level', function () {
    expect(Schema::hasColumn('skills', 'level'))->toBeFalse();
});

test('the blog link and feed only appear once an article is published', function () {
    Post::factory()->create(['is_published' => false, 'published_at' => null]);

    $this->get('/')
        ->assertDontSee('href="'.route('landing.blog').'"', false)
        ->assertDontSee(route('feed.rss'));
    $this->get(route('sitemap'))->assertDontSee(route('landing.blog').'<');

    Post::factory()->create(['is_published' => true, 'published_at' => now()]);

    $this->get('/')
        ->assertSee('href="'.route('landing.blog').'"', false)
        ->assertSee(route('feed.rss'));
    $this->get(route('sitemap'))->assertSee(route('landing.blog').'<', false);
});

test('home sections are numbered without gaps when one is hidden', function () {
    Project::factory()->featured()->create();
    Skill::factory()->create();

    preg_match_all('/<span class="index-number">(\d+)<\/span>\s*<h2[^>]*>([^<]+)<\/h2>/', $this->get('/')->getContent(), $sections, PREG_SET_ORDER);

    expect(array_map(fn (array $section) => "{$section[1]} {$section[2]}", $sections))
        ->toBe(['01 Selected Work', '02 Skills']);
});

test('a project without an image shows no empty plate', function () {
    Project::factory()->create(['slug' => 'plain', 'title' => 'Plain Project', 'image' => null]);

    $this->get('/projects/plain')->assertOk()->assertDontSee('aspect-16/9', false);
});

test('the skills page lists names without self-rated scores', function () {
    Skill::factory()->create(['name' => 'Laravel', 'category' => 'Backend']);

    $this->get(route('landing.skills'))
        ->assertOk()
        ->assertSee('Laravel')
        ->assertDontSee('style="width:', false);
});
