<?php

use App\Models\Post;
use App\Models\Project;
use App\Models\SiteContent;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

test('nothing is reported when every image is still referenced', function () {
    Storage::disk('public')->put('posts/used.jpg', 'x');
    Post::factory()->create(['thumbnail' => 'posts/used.jpg']);

    $this->artisan('images:prune')
        ->expectsOutputToContain('No orphaned images found.')
        ->assertSuccessful();

    Storage::disk('public')->assertExists('posts/used.jpg');
});

test('orphans are listed but kept without --force', function () {
    Storage::disk('public')->put('posts/orphan.jpg', 'x');

    $this->artisan('images:prune')
        ->expectsOutputToContain('posts/orphan.jpg')
        ->expectsOutputToContain('--force')
        ->assertSuccessful();

    Storage::disk('public')->assertExists('posts/orphan.jpg');
});

test('--force deletes only the unreferenced files', function () {
    $disk = Storage::disk('public');

    $disk->put('posts/keep-thumbnail.jpg', 'x');
    $disk->put('posts/content/keep-inline.jpg', 'x');
    $disk->put('projects/keep-project.jpg', 'x');
    $disk->put('about/keep-photo.jpg', 'x');

    $disk->put('posts/orphan-a.jpg', 'x');
    $disk->put('posts/content/orphan-b.jpg', 'x');
    $disk->put('projects/orphan-c.jpg', 'x');
    $disk->put('about/orphan-d.jpg', 'x');

    Post::factory()->create([
        'thumbnail' => 'posts/keep-thumbnail.jpg',
        'content' => '<p><img src="http://localhost/storage/posts/content/keep-inline.jpg"></p>',
    ]);
    Project::factory()->create(['image' => 'projects/keep-project.jpg']);
    SiteContent::set('about', 'photo', 'about/keep-photo.jpg');

    $this->artisan('images:prune --force')->assertSuccessful();

    $disk->assertExists('posts/keep-thumbnail.jpg');
    $disk->assertExists('posts/content/keep-inline.jpg');
    $disk->assertExists('projects/keep-project.jpg');
    $disk->assertExists('about/keep-photo.jpg');

    $disk->assertMissing('posts/orphan-a.jpg');
    $disk->assertMissing('posts/content/orphan-b.jpg');
    $disk->assertMissing('projects/orphan-c.jpg');
    $disk->assertMissing('about/orphan-d.jpg');
});

test('images belonging to draft posts are never pruned', function () {
    Storage::disk('public')->put('posts/draft.jpg', 'x');

    Post::factory()->create([
        'thumbnail' => 'posts/draft.jpg',
        'is_published' => false,
        'published_at' => null,
    ]);

    $this->artisan('images:prune --force')->assertSuccessful();

    Storage::disk('public')->assertExists('posts/draft.jpg');
});
