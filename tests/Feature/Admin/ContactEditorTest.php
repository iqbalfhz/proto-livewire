<?php

use App\Models\SiteContent;
use App\Models\User;
use Livewire\Livewire;

test('an admin can edit the public contact details', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('admin.contact'))->assertOk();

    Livewire::test('pages::admin.contact-editor')
        ->set('subtitle', 'Let us build something.')
        ->set('email', 'halo@example.com')
        ->set('location', 'Jakarta, Indonesia')
        ->set('availability', 'Open for freelance work')
        ->call('save')
        ->assertHasNoErrors();

    expect(SiteContent::group('contact'))->toMatchArray([
        'subtitle' => 'Let us build something.',
        'email' => 'halo@example.com',
        'location' => 'Jakarta, Indonesia',
        'availability' => 'Open for freelance work',
    ]);
});

test('saved contact details show up on the public contact page', function () {
    SiteContent::set('contact', 'email', 'halo@example.com');
    SiteContent::set('contact', 'location', 'Jakarta, Indonesia');

    $this->get(route('landing.contact'))
        ->assertOk()
        ->assertSee('halo@example.com')
        ->assertSee('Jakarta, Indonesia');
});

test('the contact editor rejects an invalid email', function () {
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test('pages::admin.contact-editor')
        ->set('email', 'not-an-email')
        ->call('save')
        ->assertHasErrors(['email' => 'email']);
});

test('non-admins cannot edit contact details', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.contact'))->assertForbidden();
});
