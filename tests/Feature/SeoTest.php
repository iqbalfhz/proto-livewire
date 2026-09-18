<?php

use App\Models\Post;
use App\Models\User;

test('the sitemap lists static pages and published posts only', function () {
    $published = Post::factory()->published()->create(['slug' => 'visible-post']);
    Post::factory()->create([
        'slug' => 'hidden-draft',
        'is_published' => false,
        'published_at' => null,
    ]);

    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->assertSee(route('landing.home'), escape: false)
        ->assertSee(route('landing.blog.show', $published->slug), escape: false)
        ->assertDontSee('hidden-draft');
});

test('the rss feed only exposes published posts', function () {
    Post::factory()->published()->create(['title' => 'Public Article']);
    Post::factory()->create([
        'title' => 'Private Draft',
        'is_published' => false,
        'published_at' => null,
    ]);

    $this->get('/feed.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
        ->assertSee('<rss version="2.0"', escape: false)
        ->assertSee('Public Article')
        ->assertDontSee('Private Draft');
});

test('robots.txt points crawlers at the sitemap and away from the admin panel', function () {
    $this->get('/robots.txt')
        ->assertOk()
        ->assertSee('Disallow: /admin')
        ->assertSee('Sitemap: '.route('sitemap'), escape: false);
});

test('public pages expose canonical and social metadata', function () {
    $post = Post::factory()->published()->create([
        'title' => 'Shareable Post',
        'slug' => 'shareable-post',
        'excerpt' => 'A short summary for the preview card.',
    ]);

    $this->get(route('landing.blog.show', $post->slug))
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.route('landing.blog.show', $post->slug).'"', escape: false)
        ->assertSee('name="description" content="A short summary for the preview card."', escape: false)
        ->assertSee('property="og:type" content="article"', escape: false)
        ->assertSee('application/ld+json', escape: false);
});

test('the admin panel is never indexed', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('name="robots" content="noindex, nofollow"', escape: false)
        ->assertDontSee('rel="canonical"', escape: false);
});
