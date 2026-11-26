<?php

use App\Models\Post;
use App\Models\Project;
use App\Models\SiteContent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->admin()->create());
});

test('deleting a post removes its thumbnail', function () {
    $post = Post::factory()->create(['thumbnail' => 'posts/thumb.jpg']);
    Storage::disk('public')->put('posts/thumb.jpg', 'x');

    Livewire::test('pages::admin.blog-index')->call('delete', $post->id);

    Storage::disk('public')->assertMissing('posts/thumb.jpg');
});

test('deleting a post removes images embedded in its body', function () {
    Storage::disk('public')->put('posts/content/inline.jpg', 'x');

    $post = Post::factory()->create([
        'content' => '<p>See <img src="http://localhost/storage/posts/content/inline.jpg"> here.</p>',
    ]);

    Livewire::test('pages::admin.blog-index')->call('delete', $post->id);

    Storage::disk('public')->assertMissing('posts/content/inline.jpg');
});

test('an embedded image still used by another post is kept', function () {
    Storage::disk('public')->put('posts/content/shared.jpg', 'x');

    $body = '<p><img src="http://localhost/storage/posts/content/shared.jpg"></p>';
    $first = Post::factory()->create(['content' => $body]);
    Post::factory()->create(['content' => $body]);

    Livewire::test('pages::admin.blog-index')->call('delete', $first->id);

    Storage::disk('public')->assertExists('posts/content/shared.jpg');
});

test('replacing a post thumbnail deletes the previous file', function () {
    $post = Post::factory()->create(['thumbnail' => 'posts/old.jpg']);
    Storage::disk('public')->put('posts/old.jpg', 'x');

    Livewire::test('pages::admin.blog-form', ['post' => $post])
        ->set('thumbnail', UploadedFile::fake()->image('new.jpg'))
        ->call('save')
        ->assertHasNoErrors();

    Storage::disk('public')->assertMissing('posts/old.jpg');
    expect($post->refresh()->thumbnail)->not->toBe('posts/old.jpg');
    Storage::disk('public')->assertExists($post->thumbnail);
});

test('deleting a project removes its image', function () {
    $project = Project::factory()->create(['image' => 'projects/shot.jpg']);
    Storage::disk('public')->put('projects/shot.jpg', 'x');

    Livewire::test('pages::admin.projects-index')->call('delete', $project->id);

    Storage::disk('public')->assertMissing('projects/shot.jpg');
});

test('the about photo is cropped in the browser before anything is uploaded', function () {
    Livewire::actingAs(User::factory()->admin()->create())
        ->test('pages::admin.about-editor')
        ->assertSeeHtml('x-data="photoCropper()"')
        ->assertSeeHtml('x-on:change="pick($event)"')
        ->assertDontSeeHtml('wire:model="photo"');
});

test('the cropped about photo is saved as soon as it uploads and replaces the previous file', function () {
    SiteContent::set('about', 'photo', 'about/old.jpg');
    SiteContent::set('about', 'name', 'Saved Name');
    Storage::disk('public')->put('about/old.jpg', 'x');

    Livewire::test('pages::admin.about-editor')
        ->set('name', 'Unsaved draft')
        ->upload('photo', [UploadedFile::fake()->image('profile-photo.jpg', 800, 1000)])
        ->assertHasNoErrors()
        ->assertSet('photo', null);

    $path = SiteContent::get('about', 'photo');
    expect($path)->toStartWith('about/')->not->toBe('about/old.jpg');
    Storage::disk('public')->assertExists($path);
    Storage::disk('public')->assertMissing('about/old.jpg');
    expect(SiteContent::get('about', 'name'))->toBe('Saved Name');
});

test('an about photo that is not an image is rejected and nothing changes', function () {
    SiteContent::set('about', 'photo', 'about/old.jpg');
    Storage::disk('public')->put('about/old.jpg', 'x');

    Livewire::test('pages::admin.about-editor')
        ->set('photo', UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf'))
        ->assertHasErrors('photo')
        ->assertSee('The photo field must be a file of type');

    expect(SiteContent::get('about', 'photo'))->toBe('about/old.jpg');
    Storage::disk('public')->assertExists('about/old.jpg');
});
