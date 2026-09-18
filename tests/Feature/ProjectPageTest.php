<?php

use App\Models\Project;
use Livewire\Livewire;

test('a project detail page can be reached by slug', function () {
    $project = Project::factory()->create([
        'title' => 'Portfolio Site',
        'slug' => 'portfolio-site',
        'description' => 'A long description that would have been truncated on the listing page.',
        'tech_stack' => ['Laravel', 'Livewire'],
        'demo_url' => 'https://example.test',
        'repo_url' => 'https://github.com/example/repo',
    ]);

    $this->get(route('landing.projects.show', 'portfolio-site'))
        ->assertOk()
        ->assertSee($project->title)
        ->assertSee('A long description that would have been truncated on the listing page.')
        ->assertSee('Laravel')
        ->assertSee('https://example.test', escape: false)
        ->assertSee('https://github.com/example/repo', escape: false);
});

test('an unknown project slug is a 404', function () {
    $this->get(route('landing.projects.show', 'does-not-exist'))->assertNotFound();
});

test('the projects listing links each card to its detail page', function () {
    Project::factory()->create(['slug' => 'first-project']);

    $this->get(route('landing.projects'))
        ->assertOk()
        ->assertSee(route('landing.projects.show', 'first-project'), escape: false);
});

test('the detail page suggests other projects but not itself', function () {
    Project::factory()->create(['title' => 'The Current One', 'slug' => 'current']);
    Project::factory()->create(['title' => 'Another Build', 'slug' => 'another']);

    $suggested = Livewire::test('pages::landing.project-show', ['slug' => 'current'])
        ->assertSee('Another Build')
        ->viewData('moreProjects');

    expect(collect($suggested)->pluck('slug')->all())->toBe(['another']);
});

test('the detail page carries its own social metadata', function () {
    Project::factory()->create([
        'title' => 'Shareable Project',
        'slug' => 'shareable',
        'description' => 'Worth sharing on social media.',
    ]);

    $this->get(route('landing.projects.show', 'shareable'))
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.route('landing.projects.show', 'shareable').'"', escape: false)
        ->assertSee('name="description" content="Worth sharing on social media."', escape: false)
        ->assertSee('CreativeWork', escape: false);
});

test('a tech badge on the detail page links back to the filtered listing', function () {
    Project::factory()->create(['slug' => 'tagged', 'tech_stack' => ['Livewire']]);

    $this->get(route('landing.projects.show', 'tagged'))
        ->assertOk()
        ->assertSee(route('landing.projects', ['tech' => 'Livewire']), escape: false);
});
