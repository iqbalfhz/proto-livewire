<?php

use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Project;
use App\Models\User;

/**
 * Livewire serialises every public property into the page and ships it back on
 * each request. These guard against listing pages quietly embedding full
 * records — article bodies especially — in their payload.
 */
$needle = 'UNIQUE-BODY-TEXT-THAT-ONLY-LIVES-IN-THE-ARTICLE';

test('the home page does not embed article bodies', function () use ($needle) {
    Post::factory()->published()->create([
        'title' => 'Visible Title',
        'content' => "<p>{$needle}</p>",
    ]);

    $this->get(route('landing.home'))
        ->assertOk()
        ->assertSee('Visible Title')
        ->assertDontSee($needle);
});

test('a blog post does not embed the bodies of related posts', function () use ($needle) {
    $post = Post::factory()->published()->create(['slug' => 'the-one-being-read']);
    Post::factory()->published()->create([
        'title' => 'Related Title',
        'content' => "<p>{$needle}</p>",
    ]);

    $this->get(route('landing.blog.show', $post->slug))
        ->assertOk()
        ->assertSee('Related Title')
        ->assertDontSee($needle);
});

test('the projects page keeps only its filters as component state', function () {
    Project::factory(3)->create();

    $html = $this->get(route('landing.projects'))->assertOk()->getContent();

    // The snapshot Livewire embeds should carry the two filters and nothing else.
    expect($html)->toContain('wire:snapshot')
        ->and($html)->not->toContain('&quot;projects&quot;:[{')
        ->and($html)->not->toContain('&quot;techs&quot;:[');
});

test('the admin dashboard does not embed message contents', function () use ($needle) {
    $this->actingAs(User::factory()->admin()->create());

    ContactMessage::create([
        'name' => 'Budi',
        'email' => 'budi@example.com',
        'subject' => 'Halo',
        'message' => $needle,
    ]);

    $html = $this->get(route('admin.dashboard'))->assertOk()->getContent();

    expect($html)->toContain('Budi')
        ->and($html)->not->toContain('&quot;'.$needle.'&quot;');
});
