<?php

use App\Models\Post;
use App\Models\Project;
use App\Support\OpenGraphCard;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('local'));

test('a project gets a 1200 by 630 png card without any cookies', function () {
    Project::factory()->create(['slug' => 'queue-system', 'title' => 'Queue System']);

    $response = $this->get(route('og.image', ['type' => 'projects', 'slug' => 'queue-system']))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');

    [$width, $height] = getimagesizefromstring($response->getContent());

    expect([$width, $height])->toBe([1200, 630])
        ->and($response->headers->getCookies())->toBeEmpty();
});

test('a draft article has no public card', function () {
    Post::factory()->create(['slug' => 'draft', 'is_published' => false, 'published_at' => null]);

    $this->get(route('og.image', ['type' => 'posts', 'slug' => 'draft']))->assertNotFound();
});

test('an unknown type is rejected', function () {
    $this->get('/og/users/someone.png')->assertNotFound();
});

test('the card is cached and replaced when the title changes', function () {
    $project = Project::factory()->create(['slug' => 'cached', 'title' => 'First Title']);
    $url = route('og.image', ['type' => 'projects', 'slug' => 'cached']);

    $this->get($url)->assertOk();
    $this->get($url)->assertOk();
    expect(Storage::disk('local')->files('og'))->toHaveCount(1);

    $before = Storage::disk('local')->files('og')[0];
    $project->update(['title' => 'Second Title']);
    $this->get($url)->assertOk();

    expect(Storage::disk('local')->files('og'))->toHaveCount(1)
        ->and(Storage::disk('local')->files('og')[0])->not->toBe($before);
});

test('very long or unbroken titles still render', function (string $title) {
    $png = OpenGraphCard::render('Article · 01 Jan 2026', $title, 'Someone · Developer');

    expect(getimagesizefromstring($png)[0])->toBe(1200);
})->with([
    'long' => [str_repeat('A rather long title about queues ', 8)],
    'unbroken' => [str_repeat('x', 255)],
    'unicode' => ['Membangun sistem antrean — cepat & andal'],
]);

test('detail pages point their share image at the card', function () {
    Project::factory()->create(['slug' => 'shared']);
    Post::factory()->create(['slug' => 'shared-post', 'is_published' => true, 'published_at' => now()]);

    $this->get('/projects/shared')->assertSee('/og/projects/shared.png', false);
    $this->get('/blog/shared-post')->assertSee('/og/posts/shared-post.png', false);
});
