<?php

use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('a post can be created', function () {
    Livewire::test('pages::admin.blog-form')
        ->set('title', 'My First Post')
        ->set('slug', 'my-first-post')
        ->set('content', '<p>Hello world.</p>')
        ->set('is_published', true)
        ->call('save')
        ->assertHasNoErrors();

    $post = Post::firstWhere('slug', 'my-first-post');

    expect($post)->not->toBeNull()
        ->and($post->is_published)->toBeTrue()
        ->and($post->published_at)->not->toBeNull();
});

test('a duplicate slug is rejected instead of crashing', function () {
    Post::factory()->create(['slug' => 'taken']);

    Livewire::test('pages::admin.blog-form')
        ->set('title', 'Another Post')
        ->set('slug', 'taken')
        ->set('content', '<p>Body.</p>')
        ->call('save')
        ->assertHasErrors(['slug' => 'unique']);

    expect(Post::where('slug', 'taken')->count())->toBe(1);
});

test('a post keeps its own slug when saved unchanged', function () {
    $post = Post::factory()->create(['slug' => 'keep-me']);

    Livewire::test('pages::admin.blog-form', ['post' => $post])
        ->call('save')
        ->assertHasNoErrors();

    expect($post->refresh()->slug)->toBe('keep-me');
});

test('editing the title does not silently change a published url', function () {
    $post = Post::factory()->published()->create([
        'title' => 'Original Title',
        'slug' => 'original-title',
    ]);

    Livewire::test('pages::admin.blog-form', ['post' => $post])
        ->set('title', 'A Completely New Title')
        ->call('save')
        ->assertHasNoErrors();

    $post->refresh();

    expect($post->title)->toBe('A Completely New Title')
        ->and($post->slug)->toBe('original-title');
});

test('publishing a post from the index sets the published date once', function () {
    $post = Post::factory()->create(['is_published' => false, 'published_at' => null]);

    Livewire::test('pages::admin.blog-index')->call('togglePublish', $post->id);

    $firstPublishedAt = $post->refresh()->published_at;

    expect($post->is_published)->toBeTrue()
        ->and($firstPublishedAt)->not->toBeNull();

    Livewire::test('pages::admin.blog-index')->call('togglePublish', $post->id);
    Livewire::test('pages::admin.blog-index')->call('togglePublish', $post->id);

    expect($post->refresh()->is_published)->toBeTrue()
        ->and($post->published_at->timestamp)->toBe($firstPublishedAt->timestamp);
});

test('a post can be deleted', function () {
    $post = Post::factory()->create();

    Livewire::test('pages::admin.blog-index')->call('delete', $post->id);

    $this->assertDatabaseMissing('posts', ['id' => $post->id]);
});

test('non-admins cannot reach the post form', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.blog.create'))->assertForbidden();
});
