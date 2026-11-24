<?php

use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

test('an admin can write a case study', function () {
    $project = Project::factory()->create();

    Livewire::actingAs(User::factory()->admin()->create())
        ->test('pages::admin.projects-form', ['project' => $project])
        ->set('role', 'Lead developer')
        ->set('year', '2025')
        ->set('problem', 'Orders were lost at peak.')
        ->set('approach', 'Moved writes onto a queue.')
        ->set('outcome', 'No lost orders since launch.')
        ->call('save')
        ->assertHasNoErrors();

    expect($project->refresh())
        ->role->toBe('Lead developer')
        ->year->toBe('2025')
        ->outcome->toBe('No lost orders since launch.');
});

test('the public page shows the filled case study sections', function () {
    Project::factory()->create([
        'slug' => 'case',
        'role' => 'Lead developer',
        'year' => '2025',
        'problem' => 'Orders were lost at peak.',
        'approach' => null,
        'outcome' => 'No lost orders since launch.',
    ]);

    $this->get('/projects/case')
        ->assertOk()
        ->assertSee('Lead developer')
        ->assertSee('The problem')
        ->assertSee('The outcome')
        ->assertDontSee('The approach');
});

test('case study text is escaped', function () {
    Project::factory()->create(['slug' => 'xss', 'problem' => '<script>alert(1)</script>']);

    $this->get('/projects/xss')->assertOk()->assertDontSee('<script>alert(1)</script>', false);
});
