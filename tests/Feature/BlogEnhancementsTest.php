<?php

use App\Models\Post;
use App\Models\User;
use App\Support\ArticleBody;
use Livewire\Livewire;

function publishedPost(array $attributes = []): Post
{
    return Post::factory()->create(['is_published' => true, 'published_at' => now(), ...$attributes]);
}

test('headings get unique anchors for the table of contents', function () {
    $body = ArticleBody::from('<h2>Setup</h2><p>a</p><h3>Setup</h3><h2>Déjà vu</h2>');

    expect($body->headings)->toBe([
        ['id' => 'section-setup', 'text' => 'Setup', 'level' => 2],
        ['id' => 'section-setup-2', 'text' => 'Setup', 'level' => 3],
        ['id' => 'section-deja-vu', 'text' => 'Déjà vu', 'level' => 2],
    ])->and($body->html)->toContain('<h2 id="section-setup">');
});

test('editor code lines become one escaped code block', function () {
    $body = ArticleBody::from(
        '<div class="ql-code-block-container"><div class="ql-code-block">$a = 1;</div><div class="ql-code-block"><br></div><div class="ql-code-block">echo "&lt;script&gt;";</div></div>'
    );

    expect($body->html)
        ->toContain('<pre><code>$a = 1;'."\n\n".'echo "&lt;script&gt;";</code></pre>')
        ->not->toContain('ql-code-block');
});

test('the article page shows a table of contents when it has enough headings', function () {
    publishedPost(['slug' => 'long', 'content' => '<h2>First</h2><p>a</p><h2>Second</h2><p>b</p>']);
    publishedPost(['slug' => 'short', 'content' => '<p>Just text.</p>']);

    $this->get('/blog/long')->assertOk()->assertSee('On this page')->assertSee('href="#section-second"', false);
    $this->get('/blog/short')->assertOk()->assertDontSee('On this page');
});

test('the blog can be filtered by tag', function () {
    publishedPost(['title' => 'Tagged Post', 'tags' => ['Laravel']]);
    publishedPost(['title' => 'Other Post', 'tags' => ['Design']]);

    Livewire::test('pages::landing.blog')
        ->assertSee('Tagged Post')
        ->set('tag', 'Laravel')
        ->assertSee('Tagged Post')
        ->assertDontSee('Other Post');
});

test('tags from drafts never appear publicly', function () {
    Post::factory()->create(['is_published' => false, 'published_at' => null, 'tags' => ['SecretTopic']]);
    publishedPost(['tags' => ['Laravel']]);

    $this->get(route('landing.blog'))->assertSee('Laravel')->assertDontSee('SecretTopic');
});

test('older and newer links follow publication order and skip drafts', function () {
    publishedPost(['title' => 'Oldest', 'published_at' => now()->subDays(3)]);
    publishedPost(['title' => 'Middle', 'slug' => 'middle', 'published_at' => now()->subDays(2)]);
    Post::factory()->create(['title' => 'Hidden Draft', 'is_published' => false, 'published_at' => now()->subDay()]);
    publishedPost(['title' => 'Newest', 'published_at' => now()]);

    $this->get('/blog/middle')
        ->assertOk()
        ->assertSeeInOrder(['Older', 'Oldest', 'Newer', 'Newest'])
        ->assertDontSee('Hidden Draft');
});

test('an admin can save tags on a post', function () {
    $post = publishedPost();

    Livewire::actingAs(User::factory()->admin()->create())
        ->test('pages::admin.blog-form', ['post' => $post])
        ->set('tags_input', 'Laravel, Performance, Laravel')
        ->call('save')
        ->assertHasNoErrors();

    expect($post->refresh()->tags)->toBe(['Laravel', 'Performance']);
});
