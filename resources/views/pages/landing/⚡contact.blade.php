<?php

use App\Models\ContactMessage;
use App\Models\SiteContent;
use Flux\Flux;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Contact')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $subject = '';
    public string $message = '';
    public bool $sent = false;

    public function send(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::create($validated);

        $adminEmail = config('mail.admin_address', config('mail.from.address'));
        if ($adminEmail) {
            Mail::raw("New contact message from {$validated['name']} <{$validated['email']}>\n\nSubject: {$validated['subject']}\n\n{$validated['message']}", function ($m) use ($validated, $adminEmail) {
                $m->to($adminEmail)
                    ->subject('New Contact Message: ' . ($validated['subject'] ?: 'No Subject'))
                    ->replyTo($validated['email'], $validated['name']);
            });
        }

        $this->reset('name', 'email', 'subject', 'message');
        $this->sent = true;
        Flux::toast(variant: 'success', text: 'Message sent! I\'ll get back to you soon.');
    }

    public function render()
    {
        $info = SiteContent::group('contact');
        return view('pages.landing.contact', ['info' => $info])->layout('layouts.landing');
    }
}; ?>

<div class="max-w-5xl mx-auto px-6 py-16">
    <div class="mb-12 text-center">
        <flux:heading size="xl" class="mb-3">Get In Touch</flux:heading>
        <flux:subheading class="max-w-xl mx-auto">
            {{ $info['subtitle'] ?? 'Have a question or want to work together? Drop me a message.' }}
        </flux:subheading>
    </div>

    <div class="grid md:grid-cols-5 gap-12">
        {{-- Contact Info --}}
        <div class="md:col-span-2 space-y-6">
            @if ($info['email'] ?? null)
                <div class="flex items-start gap-4">
                    <div
                        class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center shrink-0">
                        <flux:icon name="envelope" class="size-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1">Email</p>
                        <a href="mailto:{{ $info['email'] }}"
                            class="text-sm text-zinc-500 dark:text-zinc-400 hover:text-blue-600 dark:hover:text-blue-400 transition">
                            {{ $info['email'] }}
                        </a>
                    </div>
                </div>
            @endif

            @if ($info['location'] ?? null)
                <div class="flex items-start gap-4">
                    <div
                        class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center shrink-0">
                        <flux:icon name="map-pin" class="size-5 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1">Location</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $info['location'] }}</p>
                    </div>
                </div>
            @endif

            @if ($info['availability'] ?? null)
                <div class="flex items-start gap-4">
                    <div
                        class="w-10 h-10 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center shrink-0">
                        <flux:icon name="clock" class="size-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium mb-1">Availability</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $info['availability'] }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Form --}}
        <div
            class="md:col-span-3 bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 p-8">
            @if ($sent)
                <div class="text-center py-10">
                    <div
                        class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                        <flux:icon name="check" class="size-8 text-green-600 dark:text-green-400" />
                    </div>
                    <flux:heading class="mb-2">Message Sent!</flux:heading>
                    <flux:subheading class="mb-6">Thanks for reaching out. I'll get back to you soon.
                    </flux:subheading>
                    <flux:button wire:click="$set('sent', false)" variant="outline">Send Another</flux:button>
                </div>
            @else
                <form wire:submit="send" class="space-y-5">
                    <div class="grid sm:grid-cols-2 gap-5">
                        <flux:input wire:model="name" label="Your Name" placeholder="John Doe" required />
                        <flux:input wire:model="email" type="email" label="Email Address"
                            placeholder="john@example.com" required />
                    </div>
                    <flux:input wire:model="subject" label="Subject" placeholder="What's this about?" />
                    <flux:textarea wire:model="message" label="Message" placeholder="Your message..." rows="5"
                        required />
                    <flux:button type="submit" variant="primary" class="w-full" wire:loading.attr="disabled">
                        <span wire:loading.remove>Send Message</span>
                        <span wire:loading>Sending...</span>
                    </flux:button>
                </form>
            @endif
        </div>
    </div>
</div>
