<?php

use App\Models\Post;
use Illuminate\Support\Facades\DB;

/**
 * The sanitiser runs on assignment, so seeding dirty content means writing past
 * the model — exactly the state posts written before it existed are in.
 */
function seedRawContent(Post $post, string $html): void
{
    DB::table('posts')->where('id', $post->id)->update(['content' => $html]);
}

test('a clean archive reports nothing to do', function () {
    Post::factory()->create(['content' => '<p>Already tidy.</p>']);

    $this->artisan('posts:sanitize')
        ->expectsOutputToContain('Every post is already clean.')
        ->assertSuccessful();
});

test('artefacts are reported but left alone without --force', function () {
    $post = Post::factory()->create(['title' => 'Pasted From The Web']);
    seedRawContent($post, '<p><span style="background-color: rgb(255, 255, 255)">Text</span></p>');

    $this->artisan('posts:sanitize')
        ->expectsOutputToContain('Pasted From The Web')
        ->expectsOutputToContain('--force')
        ->assertSuccessful();

    expect($post->fresh()->getRawOriginal('content'))->toContain('background-color');
});

test('--force writes the cleaned content back', function () {
    $post = Post::factory()->create();
    seedRawContent($post, '<p>Keep</p><script>alert(1)</script><p><span style="background-color: #fff">Tidy</span></p>');

    $this->artisan('posts:sanitize --force')->assertSuccessful();

    $content = $post->fresh()->getRawOriginal('content');

    expect($content)->toBe('<p>Keep</p><p><span>Tidy</span></p>');
});

test('cleaning does not bump the updated timestamp', function () {
    $post = Post::factory()->create();
    seedRawContent($post, '<p><span style="background-color: white">Text</span></p>');

    $before = $post->fresh()->updated_at;

    $this->travel(2)->days();
    $this->artisan('posts:sanitize --force')->assertSuccessful();

    expect($post->fresh()->updated_at->timestamp)->toBe($before->timestamp);
});

test('deliberate formatting survives the sweep', function () {
    $post = Post::factory()->create();
    seedRawContent($post, '<h2>Heading</h2><p><strong>Bold</strong> and <a href="https://example.com">a link</a>.</p>');

    $this->artisan('posts:sanitize --force')->assertSuccessful();

    expect($post->fresh()->getRawOriginal('content'))
        ->toContain('<h2>Heading</h2>')
        ->toContain('<strong>Bold</strong>')
        ->toContain('href="https://example.com"');
});
