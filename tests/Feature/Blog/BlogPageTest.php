<?php

use App\Models\Post;
use Livewire\Livewire;

test('the blog index lists published posts', function () {
    $published = Post::factory()->published()->create(['title' => 'A Published Post']);
    $draft = Post::factory()->create([
        'title' => 'A Secret Draft',
        'is_published' => false,
        'published_at' => null,
    ]);

    $this->get(route('landing.blog'))
        ->assertOk()
        ->assertSee($published->title)
        ->assertDontSee($draft->title);
});

test('searching the blog never leaks drafts', function () {
    Post::factory()->create([
        'title' => 'Draft About Laravel',
        'excerpt' => 'Everything about laravel internals.',
        'is_published' => false,
        'published_at' => null,
    ]);

    // The search term matches the draft's excerpt, which used to break out of
    // the is_published constraint through an ungrouped orWhere().
    Livewire::test('pages::landing.blog')
        ->set('search', 'laravel')
        ->assertDontSee('Draft About Laravel');
});

test('searching the blog matches both title and excerpt of published posts', function () {
    Post::factory()->published()->create(['title' => 'Livewire Tips', 'excerpt' => 'Nothing to see.']);
    Post::factory()->published()->create(['title' => 'Unrelated', 'excerpt' => 'A deep dive into livewire.']);
    Post::factory()->published()->create(['title' => 'Gardening', 'excerpt' => 'Tomatoes.']);

    Livewire::test('pages::landing.blog')
        ->set('search', 'livewire')
        ->assertSee('Livewire Tips')
        ->assertSee('Unrelated')
        ->assertDontSee('Gardening');
});

test('a published post can be read', function () {
    $post = Post::factory()->published()->create(['slug' => 'hello-world']);

    $this->get(route('landing.blog.show', 'hello-world'))
        ->assertOk()
        ->assertSee($post->title);
});

test('a draft post cannot be read directly', function () {
    Post::factory()->create([
        'slug' => 'not-ready',
        'is_published' => false,
        'published_at' => null,
    ]);

    $this->get(route('landing.blog.show', 'not-ready'))->assertNotFound();
});
