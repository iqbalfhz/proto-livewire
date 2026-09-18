<?php

use App\Jobs\SendContactNotification;
use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

beforeEach(function () {
    RateLimiter::clear('contact|127.0.0.1');
});

test('the contact page renders', function () {
    $this->get(route('landing.contact'))->assertOk();
});

test('a visitor can send a message', function () {
    Queue::fake();

    Livewire::test('pages::landing.contact')
        ->set('name', 'Budi')
        ->set('email', 'budi@example.com')
        ->set('subject', 'Halo')
        ->set('message', 'Saya tertarik bekerja sama.')
        ->call('send')
        ->assertHasNoErrors()
        ->assertSet('sent', true);

    $this->assertDatabaseHas('contact_messages', [
        'email' => 'budi@example.com',
        'subject' => 'Halo',
        'read_at' => null,
    ]);

    Queue::assertPushed(SendContactNotification::class);
});

test('the contact form validates its input', function () {
    Livewire::test('pages::landing.contact')
        ->set('name', '')
        ->set('email', 'not-an-email')
        ->set('message', '')
        ->call('send')
        ->assertHasErrors(['name' => 'required', 'email' => 'email', 'message' => 'required']);

    expect(ContactMessage::count())->toBe(0);
});

test('the contact form is rate limited', function () {
    Queue::fake();

    foreach (range(1, 5) as $i) {
        Livewire::test('pages::landing.contact')
            ->set('name', 'Spammer')
            ->set('email', 'spam@example.com')
            ->set('message', "Message {$i}")
            ->call('send')
            ->assertHasNoErrors();
    }

    Livewire::test('pages::landing.contact')
        ->set('name', 'Spammer')
        ->set('email', 'spam@example.com')
        ->set('message', 'One too many')
        ->call('send')
        ->assertHasErrors('message');

    expect(ContactMessage::count())->toBe(5);
});

test('an admin can read and delete messages', function () {
    $this->actingAs(User::factory()->admin()->create());

    $message = ContactMessage::create([
        'name' => 'Budi',
        'email' => 'budi@example.com',
        'subject' => 'Halo',
        'message' => 'Saya tertarik bekerja sama.',
    ]);

    Livewire::test('pages::admin.messages-index')
        ->call('viewMessage', $message->id)
        ->assertSet('showModal', true);

    expect($message->refresh()->read_at)->not->toBeNull();

    Livewire::test('pages::admin.messages-index')->call('delete', $message->id);

    $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
});

test('guests cannot read messages', function () {
    $this->get(route('admin.messages.index'))->assertRedirect(route('login'));
});

test('a bot that fills the honeypot is silently discarded', function () {
    Queue::fake();

    Livewire::test('pages::landing.contact')
        ->set('name', 'Bot')
        ->set('email', 'bot@example.com')
        ->set('message', 'Buy cheap followers')
        ->set('website', 'http://spam.example')
        ->call('send')
        ->assertHasNoErrors()
        ->assertSet('sent', true);

    expect(ContactMessage::count())->toBe(0);
    Queue::assertNothingPushed();
});

test('the honeypot field is present but hidden from people', function () {
    $this->get(route('landing.contact'))
        ->assertOk()
        ->assertSee('wire:model="website"', escape: false)
        ->assertSee('aria-hidden="true"', escape: false);
});
