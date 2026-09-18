<?php

use App\Models\User;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('profile.edit'))->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    Livewire::test('pages::settings.profile')
        ->set('name', 'Test User')
        ->call('updateProfileInformation')
        ->assertHasNoErrors();

    expect($user->refresh()->name)->toEqual('Test User');
});

test('user can delete their account', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    Livewire::test('pages::settings.delete-user-modal')
        ->call('deleteUser')
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
});
