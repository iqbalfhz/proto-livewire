<?php

use App\Models\Project;
use App\Models\ProjectImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
});

test('screenshots appear on the public project page', function () {
    $project = Project::factory()->create(['slug' => 'nadi']);
    ProjectImage::factory()->for($project)->create(['caption' => 'Queue module', 'sort_order' => 1]);

    $this->get(route('landing.projects.show', 'nadi'))
        ->assertOk()
        ->assertSee('Queue module')
        ->assertSee('Inside');
});

test('a project with no screenshots shows no gallery', function () {
    Project::factory()->create(['slug' => 'plain']);

    $this->get(route('landing.projects.show', 'plain'))
        ->assertOk()
        ->assertDontSee('Inside');
});

test('screenshots render in their configured order', function () {
    $project = Project::factory()->create(['slug' => 'ordered']);
    ProjectImage::factory()->for($project)->create(['caption' => 'Second screen', 'sort_order' => 2]);
    ProjectImage::factory()->for($project)->create(['caption' => 'First screen', 'sort_order' => 1]);

    $this->get(route('landing.projects.show', 'ordered'))
        ->assertOk()
        ->assertSeeInOrder(['First screen', 'Second screen']);
});

test('a rejected screenshot does not block the next upload', function () {
    $this->actingAs(User::factory()->admin()->create());
    $project = Project::factory()->create();

    Livewire::test('pages::admin.projects-form', ['project' => $project])
        ->set('gallery_uploads', [UploadedFile::fake()->image('huge.png')->size(11000)])
        ->assertHasErrors('gallery_uploads.0')
        ->assertSet('gallery_uploads', [])
        ->set('gallery_uploads', [UploadedFile::fake()->image('fine.jpg')])
        ->assertHasNoErrors();

    expect($project->refresh()->images)->toHaveCount(1);
});

test('an admin can upload screenshots to a saved project', function () {
    $this->actingAs(User::factory()->admin()->create());
    $project = Project::factory()->create();

    Livewire::test('pages::admin.projects-form', ['project' => $project])
        ->set('gallery_uploads', [
            UploadedFile::fake()->image('booking.jpg'),
            UploadedFile::fake()->image('queue.jpg'),
        ])
        ->assertHasNoErrors();

    $images = $project->refresh()->images;

    expect($images)->toHaveCount(2)
        ->and($images->pluck('sort_order')->all())->toBe([1, 2]);

    $images->each(fn (ProjectImage $image) => Storage::disk('public')->assertExists($image->path));
});

test('a caption can be saved against a screenshot', function () {
    $this->actingAs(User::factory()->admin()->create());
    $project = Project::factory()->create();
    $image = ProjectImage::factory()->for($project)->create(['caption' => null]);

    Livewire::test('pages::admin.projects-form', ['project' => $project])
        ->set("gallery_captions.{$image->id}", 'Document numbering')
        ->call('saveCaption', $image->id);

    expect($image->refresh()->caption)->toBe('Document numbering');
});

test('screenshots can be reordered', function () {
    $this->actingAs(User::factory()->admin()->create());
    $project = Project::factory()->create();
    $first = ProjectImage::factory()->for($project)->create(['sort_order' => 1]);
    $second = ProjectImage::factory()->for($project)->create(['sort_order' => 2]);

    Livewire::test('pages::admin.projects-form', ['project' => $project])
        ->call('moveImage', $second->id, 'up');

    expect($project->refresh()->images->pluck('id')->all())->toBe([$second->id, $first->id]);
});

test('reordering past either end does nothing', function () {
    $this->actingAs(User::factory()->admin()->create());
    $project = Project::factory()->create();
    $first = ProjectImage::factory()->for($project)->create(['sort_order' => 1]);
    $second = ProjectImage::factory()->for($project)->create(['sort_order' => 2]);

    $component = Livewire::test('pages::admin.projects-form', ['project' => $project]);
    $component->call('moveImage', $first->id, 'up');
    $component->call('moveImage', $second->id, 'down');

    expect($project->refresh()->images->pluck('id')->all())->toBe([$first->id, $second->id]);
});

test('deleting a screenshot removes its file', function () {
    $this->actingAs(User::factory()->admin()->create());
    $project = Project::factory()->create();
    $image = ProjectImage::factory()->for($project)->create(['path' => 'projects/gallery/shot.jpg']);
    Storage::disk('public')->put('projects/gallery/shot.jpg', 'x');

    Livewire::test('pages::admin.projects-form', ['project' => $project])
        ->call('deleteImage', $image->id);

    Storage::disk('public')->assertMissing('projects/gallery/shot.jpg');
    $this->assertDatabaseMissing('project_images', ['id' => $image->id]);
});

test('deleting a project takes its screenshot files with it', function () {
    $project = Project::factory()->create(['image' => 'projects/main.jpg']);
    ProjectImage::factory()->for($project)->create(['path' => 'projects/gallery/a.jpg']);
    ProjectImage::factory()->for($project)->create(['path' => 'projects/gallery/b.jpg']);

    Storage::disk('public')->put('projects/main.jpg', 'x');
    Storage::disk('public')->put('projects/gallery/a.jpg', 'x');
    Storage::disk('public')->put('projects/gallery/b.jpg', 'x');

    $project->delete();

    Storage::disk('public')->assertMissing('projects/main.jpg');
    Storage::disk('public')->assertMissing('projects/gallery/a.jpg');
    Storage::disk('public')->assertMissing('projects/gallery/b.jpg');
    expect(ProjectImage::count())->toBe(0);
});

test('prune never deletes a screenshot that belongs to a project', function () {
    $project = Project::factory()->create();
    ProjectImage::factory()->for($project)->create(['path' => 'projects/gallery/keep.jpg']);

    Storage::disk('public')->put('projects/gallery/keep.jpg', 'x');
    Storage::disk('public')->put('projects/gallery/orphan.jpg', 'x');

    $this->artisan('images:prune --force')->assertSuccessful();

    Storage::disk('public')->assertExists('projects/gallery/keep.jpg');
    Storage::disk('public')->assertMissing('projects/gallery/orphan.jpg');
});
