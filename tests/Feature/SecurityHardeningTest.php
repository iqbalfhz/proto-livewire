<?php

use App\Jobs\SendContactNotification;
use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Project;
use App\Models\SiteContent;
use App\Models\User;
use App\Support\HtmlSanitizer;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

test('the server never reuses application state between requests', function () {
    expect(file_get_contents(base_path('Caddyfile')))->not->toContain('worker')
        ->and(file_exists(base_path('frankenphp-worker.php')))->toBeFalse();
});

test('structured data cannot break out of its script tag', function () {
    Project::factory()->create(['slug' => 'payload', 'title' => '</script><script>alert(1)</script>']);

    expect($this->get('/projects/payload')->assertOk()->getContent())
        ->not->toContain('<script>alert(1)</script>');
});

test('the hero headline keeps emphasis but loses anything executable', function () {
    SiteContent::set('home', 'headline', 'Hi, I am <span class="x" onclick="alert(1)">Iqbal</span><img src=x onerror=alert(1)>');

    $this->get('/')
        ->assertOk()
        ->assertSee('Hi, I am <span>Iqbal</span>', false)
        ->assertDontSee('onerror', false)
        ->assertDontSee('onclick', false);
});

test('rich text only keeps the classes the editor produces', function () {
    expect(HtmlSanitizer::clean('<p class="ql-align-center fixed inset-0 z-50">x</p>'))
        ->toBe('<p class="ql-align-center">x</p>')
        ->and(HtmlSanitizer::clean('<a class="fixed inset-0" href="https://example.com">x</a>'))
        ->toBe('<a href="https://example.com">x</a>');
});

test('stored image paths cannot be rewritten from the browser', function (string $component, array $params, string $property) {
    Livewire::actingAs(User::factory()->admin()->create())
        ->test($component, $params)
        ->set($property, 'about/someone-elses-photo.jpg');
})->with([
    'project' => ['pages::admin.projects-form', fn () => ['project' => Project::factory()->create()], 'existing_image'],
    'post' => ['pages::admin.blog-form', fn () => ['post' => Post::factory()->create()], 'existing_thumbnail'],
    'about' => ['pages::admin.about-editor', fn () => [], 'existing_photo'],
])->throws(CannotUpdateLockedPropertyException::class);

test('the reply link cannot smuggle extra mail headers', function () {
    $message = ContactMessage::create([
        'name' => 'Sender',
        'email' => 'sender@example.com',
        'subject' => 'x&bcc=attacker@example.com',
        'message' => 'Hello',
    ]);

    Livewire::actingAs(User::factory()->admin()->create())
        ->test('pages::admin.messages-index')
        ->call('viewMessage', $message->id)
        ->assertSeeHtml('subject=Re%3A%20x%26bcc%3Dattacker%40example.com')
        ->assertDontSeeHtml('subject=Re: x&amp;bcc');
});

test('line breaks cannot reach the notification mail headers', function (string $field) {
    Queue::fake();
    RateLimiter::clear('contact|127.0.0.1');

    Livewire::test('pages::landing.contact')
        ->set('name', 'Sender')
        ->set('email', 'sender@example.com')
        ->set('message', 'Hello')
        ->set($field, "Hello\r\nBcc: attacker@example.com")
        ->call('send')
        ->assertHasErrors([$field]);

    Queue::assertNotPushed(SendContactNotification::class);
})->with(['name', 'subject']);

test('the about page survives github being unreachable', function () {
    config(['services.github.token' => 'token', 'services.github.username' => 'someone']);
    Http::fake(fn () => throw new ConnectionException('unreachable'));

    $this->get(route('landing.about'))->assertOk();
});

test('forwarded headers are ignored unless they come from a private proxy', function () {
    Route::middleware('web')->get('/__client', fn (Request $request) => [
        'ip' => $request->ip(),
        'host' => $request->getHost(),
    ]);

    $headers = ['X-Forwarded-For' => '198.51.100.7', 'X-Forwarded-Host' => 'attacker.example'];

    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.9'])->get('/__client', $headers)
        ->assertJson(['ip' => '203.0.113.9'])
        ->assertJsonMissing(['host' => 'attacker.example']);

    $this->withServerVariables(['REMOTE_ADDR' => '10.0.1.5'])->get('/__client', $headers)
        ->assertJson(['ip' => '198.51.100.7'])
        ->assertJsonMissing(['host' => 'attacker.example']);
});

test('sign-in is closed unless the user is an admin or listed', function (string $allowed, bool $admin, bool $expected) {
    config(['auth.allowed_emails' => $allowed]);

    $user = User::factory()->state(['email' => 'Owner@Example.com', 'is_admin' => $admin])->create();

    expect($user->isAllowedToSignIn())->toBe($expected);
})->with([
    'empty list, stranger' => ['', false, false],
    'empty list, admin' => ['', true, true],
    'listed, different case' => [' owner@example.com , other@example.com', false, true],
    'not listed' => ['other@example.com', false, false],
]);

test('a passwordless user can be logged in through the session guard', function () {
    $user = User::factory()->admin()->create();

    auth()->guard('web')->login($user);

    expect(auth()->guard('web')->id())->toBe($user->id);
});

test('responses carry the hardened security headers', function () {
    $this->get('/')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-XSS-Protection', '0');
});

test('the content security policy blocks scripts without the page nonce', function () {
    $response = $this->get('/')->assertOk();
    $policy = $response->headers->get('Content-Security-Policy');

    preg_match("/'nonce-([^']+)'/", $policy, $matches);

    expect($policy)->toContain("object-src 'none'")->toContain("base-uri 'self'")
        ->not->toContain("script-src 'self' 'unsafe-inline'")
        ->and($matches[1] ?? null)->not->toBeNull()
        ->and($response->getContent())->toContain('nonce="'.$matches[1].'"');
});

test('session cookies are secure and encrypted by default in production', function () {
    $keys = ['APP_ENV', 'SESSION_ENCRYPT', 'SESSION_SECURE_COOKIE'];
    [$server, $env, $process] = [$_SERVER, $_ENV, array_map(fn (string $key) => getenv($key), array_combine($keys, $keys))];

    unset($_SERVER['SESSION_ENCRYPT'], $_ENV['SESSION_ENCRYPT'], $_SERVER['SESSION_SECURE_COOKIE'], $_ENV['SESSION_SECURE_COOKIE']);
    putenv('SESSION_ENCRYPT');
    putenv('SESSION_SECURE_COOKIE');
    $_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'production';
    putenv('APP_ENV=production');

    try {
        $session = require config_path('session.php');
    } finally {
        [$_SERVER, $_ENV] = [$server, $env];

        foreach ($process as $key => $value) {
            putenv($value === false ? $key : "{$key}={$value}");
        }
    }

    expect($session['secure'])->toBeTrue()->and($session['encrypt'])->toBeTrue();
});
