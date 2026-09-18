<?php

use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

test('a project can be created with a tech stack', function () {
    Livewire::test('pages::admin.projects-form')
        ->set('title', 'Portfolio Site')
        ->set('slug', 'portfolio-site')
        ->set('description', 'My own corner of the web.')
        ->set('tech_stack_input', 'Laravel, Livewire , Tailwind CSS')
        ->call('save')
        ->assertHasNoErrors();

    $project = Project::firstWhere('slug', 'portfolio-site');

    expect($project)->not->toBeNull()
        ->and($project->tech_stack)->toBe(['Laravel', 'Livewire', 'Tailwind CSS']);
});

test('a duplicate project slug is rejected instead of crashing', function () {
    Project::factory()->create(['slug' => 'taken']);

    Livewire::test('pages::admin.projects-form')
        ->set('title', 'Another Project')
        ->set('slug', 'taken')
        ->call('save')
        ->assertHasErrors(['slug' => 'unique']);

    expect(Project::where('slug', 'taken')->count())->toBe(1);
});

test('editing a project title keeps its slug', function () {
    $project = Project::factory()->create(['title' => 'Old Name', 'slug' => 'old-name']);

    Livewire::test('pages::admin.projects-form', ['project' => $project])
        ->set('title', 'Brand New Name')
        ->call('save')
        ->assertHasNoErrors();

    expect($project->refresh()->slug)->toBe('old-name');
});

test('a project can be featured and unfeatured', function () {
    $project = Project::factory()->create(['is_featured' => false]);

    Livewire::test('pages::admin.projects-index')->call('toggleFeatured', $project->id);
    expect($project->refresh()->is_featured)->toBeTrue();

    Livewire::test('pages::admin.projects-index')->call('toggleFeatured', $project->id);
    expect($project->refresh()->is_featured)->toBeFalse();
});

test('the public projects page can filter to featured projects', function () {
    Project::factory()->featured()->create(['title' => 'Featured Work']);
    Project::factory()->create(['title' => 'Side Experiment', 'is_featured' => false]);

    Livewire::test('pages::landing.projects')
        ->assertSee('Featured Work')
        ->assertSee('Side Experiment')
        ->set('filter', 'featured')
        ->assertSee('Featured Work')
        ->assertDontSee('Side Experiment');
});

test('non-admins cannot reach the project form', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.projects.create'))->assertForbidden();
});
