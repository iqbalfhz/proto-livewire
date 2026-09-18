<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('authenticated non-admins cannot reach the admin panel', function () {
    $this->actingAs(User::factory()->create());

    $this->get(route('admin.dashboard'))->assertForbidden();
});

test('admins can visit the dashboard', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->get(route('admin.dashboard'))->assertOk();
});

test('the team dashboard route redirects to the admin panel', function () {
    $user = User::factory()->admin()->create();

    $this->actingAs($user);

    $this->get('/'.$user->currentTeam->slug.'/dashboard')
        ->assertRedirect(route('admin.dashboard'));
});
