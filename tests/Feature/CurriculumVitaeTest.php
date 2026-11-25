<?php

use App\Enums\ExperienceType;
use App\Models\Experience;
use App\Models\Project;
use App\Models\SiteContent;
use App\Models\Skill;
use App\Models\User;
use App\Support\CurriculumVitae;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('the cv lists current work first and separates education', function () {
    Experience::factory()->create(['title' => 'Past Role', 'started_on' => '2020-01-01', 'ended_on' => '2022-01-01']);
    Experience::factory()->current()->create(['title' => 'Current Role', 'started_on' => '2023-01-01']);
    Experience::factory()->education()->create(['title' => 'Computer Science']);

    $this->get(route('landing.cv'))
        ->assertOk()
        ->assertSeeInOrder(['Experience', 'Current Role', 'Jan 2023 – Present', 'Past Role', 'Education', 'Computer Science']);
});

test('an entry that starts and ends in the same month shows that month once', function () {
    $course = Experience::factory()->education()->create(['started_on' => '2023-08-01', 'ended_on' => '2023-08-20']);

    expect($course->period())->toBe('Aug 2023');
});

test('the cv page offers a pdf download instead of the print dialog', function () {
    $this->get(route('landing.cv'))
        ->assertSee('href="'.route('landing.cv.pdf').'"', false)
        ->assertSee('Download PDF')
        ->assertDontSee('window.print()');
});

test('the cv downloads as a pdf file named after its owner', function () {
    SiteContent::set('about', 'name', 'Iqbal Fahrozi');
    Experience::factory()->create();

    $response = $this->get(route('landing.cv.pdf'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertDownload('iqbal-fahrozi-cv.pdf');

    expect($response->getContent())->toStartWith('%PDF-');
});

test('the pdf carries the same content as the cv page', function () {
    SiteContent::set('about', 'name', 'Iqbal Fahrozi');
    SiteContent::set('about', 'bio', 'Builds internal tools.');
    SiteContent::set('contact', 'email', 'halo@example.com');
    Experience::factory()->current()->create(['title' => 'IT Support', 'started_on' => '2024-05-01']);
    Experience::factory()->education()->create(['title' => 'Computer Science']);
    Project::factory()->featured()->create(['title' => 'NADI', 'role' => 'Solo developer', 'year' => '2026']);
    Skill::factory()->create(['name' => 'Laravel', 'category' => 'Web']);

    $this->view('pdf.cv', CurriculumVitae::data())->assertSeeInOrder([
        'Iqbal Fahrozi', 'halo@example.com', 'Builds internal tools.',
        'IT Support', 'May 2024 – Present', 'Computer Science',
        'NADI', 'Solo developer · 2026', 'Web', 'Laravel',
    ]);
});

test('the pdf photo is cropped to 4:5 and shrunk to a small jpeg', function () {
    Storage::fake('public');
    Storage::disk('public')->put('about/me.png', UploadedFile::fake()->image('me.png', 1600, 900)->getContent());

    $photo = CurriculumVitae::photo('about/me.png');
    $image = getimagesizefromstring(base64_decode(Str::after($photo, 'data:image/jpeg;base64,')));

    expect($photo)->toStartWith('data:image/jpeg;base64,')
        ->and([$image[0], $image[1], $image['mime']])->toBe([360, 450, 'image/jpeg']);
});

test('the pdf has no photo when none is uploaded or the file is gone', function () {
    Storage::fake('public');

    expect(CurriculumVitae::photo(null))->toBeNull()
        ->and(CurriculumVitae::photo('about/missing.jpg'))->toBeNull();

    $this->view('pdf.cv', CurriculumVitae::data())->assertDontSee('class="photo"', false);
});

test('the downloaded pdf embeds the profile photo', function () {
    Storage::fake('public');
    Storage::disk('public')->put('about/me.jpg', UploadedFile::fake()->image('me.jpg', 960, 1280)->getContent());
    SiteContent::set('about', 'photo', 'about/me.jpg');

    expect(substr_count($this->get(route('landing.cv.pdf'))->getContent(), '/Subtype /Image'))->toBe(2);
});

test('every pdf page is laid on one shared paper texture', function () {
    $this->view('pdf.cv', CurriculumVitae::data())
        ->assertSee('class="paper"', false)
        ->assertSee(resource_path('images/cv-paper.jpg'), false);

    expect(substr_count($this->get(route('landing.cv.pdf'))->getContent(), '/Subtype /Image'))->toBe(1);
});

test('the pdf embeds only the glyphs it uses', function () {
    SiteContent::set('about', 'name', 'Iqbal Fahrozi');
    Experience::factory()->create();

    expect($this->get(route('landing.cv.pdf'))->getContent())
        ->toMatch('#/BaseFont /[A-Z]+\+Newsreader#')
        ->toMatch('#/BaseFont /[A-Z]+\+JetBrainsMono#');
});

test('the pdf shows the photo beside the name', function () {
    $this->view('pdf.cv', [...CurriculumVitae::data(), 'photo' => 'data:image/jpeg;base64,AAAA'])
        ->assertSee('<img src="data:image/jpeg;base64,AAAA"', false);
});

test('pdf downloads are rate limited', function () {
    expect(Route::getRoutes()->getByName('landing.cv.pdf')->gatherMiddleware())->toContain('throttle:10,1');
});

test('the cv link only appears once there is something to show', function () {
    $this->get('/')->assertDontSee(route('landing.cv'));
    $this->get(route('sitemap'))->assertDontSee(route('landing.cv'));

    Experience::factory()->create();

    $this->get('/')->assertSee(route('landing.cv'));
    $this->get(route('sitemap'))->assertSee(route('landing.cv'));
});

test('an admin can add, edit and delete an entry', function () {
    $component = Livewire::actingAs(User::factory()->admin()->create())
        ->test('pages::admin.experience-index')
        ->set('type', 'education')
        ->set('title', 'Informatics')
        ->set('organization', 'Some University')
        ->set('started_on', '2018-08-01')
        ->set('ended_on', '2022-07-01')
        ->call('save')
        ->assertHasNoErrors();

    $entry = Experience::sole();
    expect($entry->type)->toBe(ExperienceType::Education);

    $component->call('edit', $entry->id)
        ->assertSet('title', 'Informatics')
        ->set('title', 'Computer Science')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('editingId', null);

    expect($entry->refresh()->title)->toBe('Computer Science');

    $component->call('delete', $entry->id);

    expect(Experience::count())->toBe(0);
});

test('an entry cannot end before it starts or use an unknown type', function () {
    Livewire::actingAs(User::factory()->admin()->create())
        ->test('pages::admin.experience-index')
        ->set('type', 'hobby')
        ->set('title', 'Role')
        ->set('organization', 'Company')
        ->set('started_on', '2024-01-01')
        ->set('ended_on', '2023-01-01')
        ->call('save')
        ->assertHasErrors(['type', 'ended_on']);

    expect(Experience::count())->toBe(0);
});

test('the experience editor is admin only', function () {
    $this->get(route('admin.experience.index'))->assertRedirect();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.experience.index'))
        ->assertForbidden();
});
