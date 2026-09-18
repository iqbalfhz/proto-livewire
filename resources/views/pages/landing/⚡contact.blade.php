<?php

use App\Jobs\SendContactNotification;
use App\Models\ContactMessage;
use App\Models\SiteContent;
use Flux\Flux;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Contact')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $subject = '';
    public string $message = '';
    public bool $sent = false;

    /**
     * Honeypot. Hidden from people, irresistible to naive bots — anything that
     * fills it in gets the success screen and nothing else.
     */
    public string $website = '';

    public function send(): void
    {
        if ($this->website !== '') {
            $this->reset('name', 'email', 'subject', 'message', 'website');
            $this->sent = true;

            return;
        }

        $key = 'contact|' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('message', "Too many messages. Please wait {$seconds} seconds before trying again.");

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        ContactMessage::create($validated);

        RateLimiter::hit($key, 300);

        $this->reset('name', 'email', 'subject', 'message');
        $this->sent = true;
        Flux::toast(variant: 'success', text: 'Message sent! I\'ll get back to you soon.');

        $adminEmail = config('mail.admin_address', config('mail.from.address'));
        if ($adminEmail) {
            try {
                SendContactNotification::dispatch($validated, $adminEmail);
            } catch (\Throwable $e) {
                logger()->error('Contact notification dispatch failed: ' . $e->getMessage());
            }
        }
    }

    public function render()
    {
        $info = SiteContent::group('contact');
        $hasContactInfo = !empty($info['email'] ?? '') || !empty($info['location'] ?? '') || !empty($info['availability'] ?? '');

        return $this->view(['info' => $info, 'hasContactInfo' => $hasContactInfo])->layout('layouts.landing', [
            'description' => $info['subtitle'] ?? 'Have a question or want to work together? Drop me a message.',
        ]);
    }
}; ?>

<div>
    {{-- ═══════════════════════ MASTHEAD ═══════════════════════ --}}
    <section class="border-b border-rule">
        <div class="mx-auto max-w-5xl px-6">
            <div class="flex items-center gap-4 border-b border-rule py-4">
                <span class="label">Get in touch</span>
                <span class="h-px flex-1 bg-rule"></span>
                <span class="label">Replies within a day or two</span>
            </div>

            <div class="grid items-end gap-8 py-14 md:grid-cols-12 md:py-20">
                <h1 class="display display-xl md:col-span-7" data-reveal>Contact</h1>
                <p class="text-lg leading-relaxed text-ink-soft md:col-span-5 md:pb-3" data-reveal
                    data-reveal-delay="120">
                    {{ $info['subtitle'] ?? 'Have a question or want to work together? Drop me a message.' }}
                </p>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ THE LETTER ═══════════════════════ --}}
    <section class="mx-auto max-w-5xl px-6 py-16 md:py-20">
        <div class="grid gap-12 md:grid-cols-12 md:gap-16">

            {{-- Details --}}
            @if ($hasContactInfo)
                <aside class="md:col-span-4" data-reveal>
                    <dl class="space-y-8 md:sticky md:top-32">
                        @if ($info['email'] ?? null)
                            <div class="border-t border-rule pt-4">
                                <dt class="label mb-2">Email</dt>
                                <dd>
                                    <a href="mailto:{{ $info['email'] }}"
                                        class="link-sweep text-ink-soft transition-colors hover:text-oxblood">
                                        {{ $info['email'] }}
                                    </a>
                                </dd>
                            </div>
                        @endif

                        @if ($info['location'] ?? null)
                            <div class="border-t border-rule pt-4">
                                <dt class="label mb-2">Located in</dt>
                                <dd class="text-ink-soft">{{ $info['location'] }}</dd>
                            </div>
                        @endif

                        @if ($info['availability'] ?? null)
                            <div class="border-t border-rule pt-4">
                                <dt class="label mb-2">Availability</dt>
                                <dd class="text-ink-soft">{{ $info['availability'] }}</dd>
                            </div>
                        @endif
                    </dl>
                </aside>
            @endif

            {{-- Form --}}
            <div class="{{ $hasContactInfo ? 'md:col-span-8' : 'md:col-span-8 md:col-start-3' }}" data-reveal
                data-reveal-delay="120">
                @if ($sent)
                    <div class="border-y border-rule-strong py-20 text-center">
                        <p class="index-number mb-6 block">✦</p>
                        <p class="display display-md mb-4">Message sent.</p>
                        <p class="mx-auto mb-10 max-w-sm leading-relaxed text-ink-muted">
                            Thanks for reaching out — I'll get back to you soon.
                        </p>
                        <button type="button" wire:click="$set('sent', false)"
                            class="label link-sweep link-retract hover:text-ink! transition-colors">
                            Write another
                        </button>
                    </div>
                @else
                    <form wire:submit="send" class="space-y-9">
                        {{-- Honeypot: off-screen, skipped by keyboard and screen readers alike. --}}
                        <div aria-hidden="true"
                            style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap">
                            <label for="contact-website">Leave this field empty</label>
                            <input type="text" id="contact-website" wire:model="website" tabindex="-1"
                                autocomplete="off" />
                        </div>

                        <div class="grid gap-9 sm:grid-cols-2">
                            {{-- Name --}}
                            <div>
                                <label for="contact-name" class="label mb-3 block">Your name</label>
                                <input id="contact-name" type="text" wire:model="name" required
                                    placeholder="Jane Doe"
                                    class="w-full border-0 border-b border-rule-strong bg-transparent px-0 pb-2 text-ink placeholder:text-ink-muted/60 focus:border-oxblood focus:outline-none focus:ring-0 transition-colors">
                                @error('name')
                                    <p class="label mt-2 text-oxblood!">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div>
                                <label for="contact-email" class="label mb-3 block">Email address</label>
                                <input id="contact-email" type="email" wire:model="email" required
                                    placeholder="jane@example.com"
                                    class="w-full border-0 border-b border-rule-strong bg-transparent px-0 pb-2 text-ink placeholder:text-ink-muted/60 focus:border-oxblood focus:outline-none focus:ring-0 transition-colors">
                                @error('email')
                                    <p class="label mt-2 text-oxblood!">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Subject --}}
                        <div>
                            <label for="contact-subject" class="label mb-3 block">Subject <span
                                    class="normal-case tracking-normal opacity-60">(optional)</span></label>
                            <input id="contact-subject" type="text" wire:model="subject"
                                placeholder="What's this about?"
                                class="w-full border-0 border-b border-rule-strong bg-transparent px-0 pb-2 text-ink placeholder:text-ink-muted/60 focus:border-oxblood focus:outline-none focus:ring-0 transition-colors">
                            @error('subject')
                                <p class="label mt-2 text-oxblood!">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Message --}}
                        <div>
                            <label for="contact-message" class="label mb-3 block">Message</label>
                            <textarea id="contact-message" wire:model="message" rows="6" required placeholder="Tell me what you're working on…"
                                class="w-full resize-none border-0 border-b border-rule-strong bg-transparent px-0 pb-2 leading-relaxed text-ink placeholder:text-ink-muted/60 focus:border-oxblood focus:outline-none focus:ring-0 transition-colors"></textarea>
                            @error('message')
                                <p class="label mt-2 text-oxblood!">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-5 pt-2">
                            <button type="submit" class="btn-ink" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="send">Send message</span>
                                <span wire:loading wire:target="send">Sending…</span>
                                <span aria-hidden="true">→</span>
                            </button>
                            <span class="label hidden sm:block">No newsletter, no spam.</span>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </section>
</div>
