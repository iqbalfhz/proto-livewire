<?php

use App\Models\Post;
use App\Support\MarkdownArticle;

const BLOG_API_TOKEN = 'test-token-0123456789abcdef0123456789abcdef';

beforeEach(fn () => config(['services.blog_api.token' => BLOG_API_TOKEN]));

function draftPayload(array $overrides = []): array
{
    return [
        'title' => 'Queues in Laravel',
        'excerpt' => 'Why the job ran twice.',
        'tags' => ['Laravel', 'Queues'],
        'markdown' => "## The setup\n\nA short paragraph.",
        ...$overrides,
    ];
}

test('markdown lists become the flat lists the editor writes', function () {
    $html = MarkdownArticle::toEditorHtml("- one\n- two\n  1. nested\n\n1. first\n2. second");

    expect($html)->toBe(
        '<ol><li data-list="bullet">one</li><li data-list="bullet">two</li><li data-list="ordered" class="ql-indent-1">nested</li></ol>'
        .'<ol><li data-list="ordered">first</li><li data-list="ordered">second</li></ol>'
    );
});

test('markdown code blocks become one editor line per row, escaped', function () {
    $html = MarkdownArticle::toEditorHtml("```php\nif (\$a < \$b) {\n\n}\n```");

    expect($html)->toBe(
        '<div class="ql-code-block-container"><div class="ql-code-block">if ($a &lt; $b) {</div>'
        .'<div class="ql-code-block"><br></div><div class="ql-code-block">}</div></div>'
    );
});

test('quotes, headings and inline marks follow the editor rules', function () {
    $html = MarkdownArticle::toEditorHtml("# Big\n\n> First line\n>\n> Second **line**\n\nUse `php artisan` and [docs](https://laravel.com).\n\n---");

    expect($html)->toBe(
        '<h2>Big</h2><blockquote>First line</blockquote><blockquote>Second <strong>line</strong></blockquote>'
        .'<p>Use <code>php artisan</code> and <a href="https://laravel.com">docs</a>.</p><hr>'
    );
});

test('markdown cannot smuggle in html, scripts, images or unsafe links', function () {
    $html = MarkdownArticle::toEditorHtml("<script>alert(1)</script>\n\n![pic](https://example.com/a.png)\n\n[click](javascript:alert(1)) <b onclick=\"x\">hi</b>");

    expect($html)->not->toContain('<script')
        ->not->toContain('<img')
        ->not->toContain('javascript:')
        ->not->toContain('onclick');
});

test('the api is locked without the right token', function (?string $configured, ?string $sent) {
    config(['services.blog_api.token' => $configured]);

    $this->withToken((string) $sent)->postJson(route('api.posts.store'), draftPayload())->assertUnauthorized();
    $this->withToken((string) $sent)->getJson(route('api.posts.titles'))->assertUnauthorized();

    expect(Post::count())->toBe(0);
})->with([
    'no token sent' => [BLOG_API_TOKEN, null],
    'wrong token' => [BLOG_API_TOKEN, 'wrong-token-0123456789abcdef0123456789ab'],
    'api token not configured' => [null, ''],
    'configured token too short' => ['short', 'short'],
]);

test('a new article is always saved as a draft', function () {
    $response = $this->withToken(BLOG_API_TOKEN)
        ->postJson(route('api.posts.store'), draftPayload(['is_published' => true, 'published_at' => now()]))
        ->assertCreated();

    $post = Post::sole();

    expect($post->is_published)->toBeFalse()
        ->and($post->published_at)->toBeNull()
        ->and($post->slug)->toBe('queues-in-laravel')
        ->and($post->tags)->toBe(['Laravel', 'Queues'])
        ->and($post->content)->toBe('<h2>The setup</h2><p>A short paragraph.</p>');

    $response->assertExactJson(['id' => $post->id, 'slug' => 'queues-in-laravel', 'edit_url' => route('admin.blog.edit', $post)]);
    $this->get(route('landing.blog.show', $post->slug))->assertNotFound();
});

test('a repeated title gets its own slug', function () {
    Post::factory()->create(['title' => 'Queues in Laravel', 'slug' => 'queues-in-laravel']);

    $this->withToken(BLOG_API_TOKEN)->postJson(route('api.posts.store'), draftPayload())->assertCreated();

    expect(Post::latest('id')->first()->slug)->toBe('queues-in-laravel-2');
});

test('invalid drafts are rejected as json even without an accept header', function () {
    $this->withToken(BLOG_API_TOKEN)
        ->post(route('api.posts.store'), draftPayload(['title' => '', 'tags' => ['a', 'b', 'c', 'd', 'e', 'f']]))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['title', 'tags']);

    expect(Post::count())->toBe(0);
});

test('titles include drafts so topics are not repeated', function () {
    Post::factory()->create(['title' => 'Published one', 'is_published' => true, 'published_at' => now()]);
    Post::factory()->create(['title' => 'Draft one', 'is_published' => false, 'published_at' => null]);

    $this->withToken(BLOG_API_TOKEN)->getJson(route('api.posts.titles'))
        ->assertOk()
        ->assertJsonCount(2)
        ->assertJsonFragment(['Draft one']);
});

test('the drafts api is rate limited, failed token guesses included', function () {
    foreach (range(1, 10) as $attempt) {
        $this->withToken('wrong-token-0123456789abcdef0123456789ab')->getJson(route('api.posts.titles'))->assertUnauthorized();
    }

    $this->withToken(BLOG_API_TOKEN)->getJson(route('api.posts.titles'))->assertTooManyRequests();
});
