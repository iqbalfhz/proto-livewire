<?php

use App\Models\SiteContent;
use App\Services\GithubContributionsService;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('About')] class extends Component {
    public function render()
    {
        $contributions = ['weeks' => [], 'total' => 0];

        if ($username = config('services.github.username')) {
            $contributions = app(GithubContributionsService::class)->getContributions($username);
        }

        return $this->view([
            'about' => SiteContent::group('about'),
            'contact' => SiteContent::group('contact'),
            'contributions' => $contributions['weeks'],
            'totalContributions' => $contributions['total'],
        ])->layout('layouts.landing');
    }
}; ?>

<div>
    {{-- ═══════════════════════ MASTHEAD ═══════════════════════ --}}
    <section class="border-b border-rule">
        <div class="mx-auto max-w-5xl px-6">
            <div class="flex items-center gap-4 border-b border-rule py-4">
                <span class="label">Colophon</span>
                <span class="h-px flex-1 bg-rule"></span>
                <span class="label">{{ $about['role'] ?? 'Developer' }}</span>
            </div>

            <div class="py-14 md:py-20">
                <h1 class="display display-xl" data-reveal>
                    {{ $about['name'] ?? config('app.name') }}
                </h1>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ BIO ═══════════════════════ --}}
    <section class="mx-auto max-w-5xl px-6 py-16 md:py-20">
        <div class="grid gap-12 md:grid-cols-12 md:gap-16">
            {{-- Portrait plate --}}
            <div class="md:col-span-4" data-reveal>
                <figure class="md:sticky md:top-32">
                    @if ($about['photo'] ?? null)
                        <img src="{{ Storage::disk('public')->url($about['photo']) }}" alt="Profile photo"
                            class="aspect-4/5 w-full border border-rule-strong object-cover grayscale transition-all duration-700 hover:grayscale-0">
                    @else
                        <div
                            class="flex aspect-4/5 w-full items-center justify-center border border-rule-strong bg-paper-sunk">
                            <span class="display display-lg text-rule-strong">
                                {{ Str::of($about['name'] ?? 'D')->substr(0, 1)->upper() }}
                            </span>
                        </div>
                    @endif

                    <figcaption class="mt-4 border-t border-rule pt-4">
                        <p class="label mb-3">Elsewhere</p>
                        <ul class="space-y-1.5">
                            @foreach (['github_url' => 'GitHub', 'linkedin_url' => 'LinkedIn', 'twitter_url' => 'Twitter'] as $key => $label)
                                @if ($about[$key] ?? null)
                                    <li>
                                        <a href="{{ $about[$key] }}" target="_blank" rel="noopener"
                                            class="link-sweep text-sm text-ink-soft transition-colors hover:text-oxblood">
                                            {{ $label }} ↗
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </figcaption>
                </figure>
            </div>

            {{-- The essay --}}
            <div class="md:col-span-8" data-reveal data-reveal-delay="120">
                <div class="dropcap text-lg leading-[1.8] text-ink-soft">
                    {!! nl2br(e($about['bio'] ?? 'Hello! I am a passionate developer who loves building things for the web.')) !!}
                </div>

                @if ($about['resume_url'] ?? null)
                    <div class="mt-10">
                        <a href="{{ $about['resume_url'] }}" target="_blank" rel="noopener" class="btn-ink">
                            Download résumé <span aria-hidden="true">↓</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ═══════════════════ CONTRIBUTION LEDGER ═══════════════════ --}}
    @if (count($contributions) > 0)
        <section class="border-y border-rule bg-paper-sunk">
            <div class="mx-auto max-w-5xl px-6 py-16 md:py-20">
                <div class="mb-10 flex flex-wrap items-end justify-between gap-4 border-b border-rule-strong pb-5"
                    data-reveal>
                    <div class="flex items-baseline gap-4">
                        <span class="index-number">✦</span>
                        <div>
                            <h2 class="display display-md">The Ledger</h2>
                            <p class="label mt-2">
                                {{ number_format($totalContributions) }} contributions in the last year
                            </p>
                        </div>
                    </div>

                    @if (config('services.github.username'))
                        <a href="https://github.com/{{ config('services.github.username') }}" target="_blank"
                            rel="noopener" class="label link-sweep hover:text-ink! transition-colors">
                            {{ '@' . config('services.github.username') }} ↗
                        </a>
                    @endif
                </div>

                {{-- Heatmap, squared off and inked rather than green --}}
                <div class="overflow-x-auto pb-2" data-reveal data-reveal-delay="100">
                    <div class="flex min-w-max gap-1">
                        @foreach ($contributions as $week)
                            <div class="flex flex-col gap-1">
                                @foreach ($week['contributionDays'] as $day)
                                    @php
                                        $count = $day['contributionCount'];
                                        $tone = match (true) {
                                            $count === 0 => 'bg-rule',
                                            $count <= 3 => 'bg-oxblood/25',
                                            $count <= 9 => 'bg-oxblood/50',
                                            $count <= 19 => 'bg-oxblood/75',
                                            default => 'bg-oxblood',
                                        };
                                    @endphp
                                    <div class="size-3 {{ $tone }} transition-opacity hover:opacity-60"
                                        title="{{ $day['date'] }}: {{ $count }} contribution{{ $count !== 1 ? 's' : '' }}">
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-5 flex items-center gap-2">
                    <span class="label">Less</span>
                    <div class="size-3 bg-rule"></div>
                    <div class="size-3 bg-oxblood/25"></div>
                    <div class="size-3 bg-oxblood/50"></div>
                    <div class="size-3 bg-oxblood/75"></div>
                    <div class="size-3 bg-oxblood"></div>
                    <span class="label">More</span>
                </div>
            </div>
        </section>
    @endif

    {{-- ═══════════════════════ CTA ═══════════════════════ --}}
    <section class="mx-auto max-w-5xl px-6 py-20 text-center md:py-24">
        <p class="label mb-6" data-reveal>Currently</p>
        <p class="display display-lg mx-auto mb-10 max-w-2xl" data-reveal data-reveal-delay="100">
            {{ $contact['availability'] ?? 'Open to new work and interesting problems.' }}
        </p>
        <div data-reveal data-reveal-delay="200">
            <a href="{{ route('landing.contact') }}" wire:navigate class="btn-ink">
                Say hi <span aria-hidden="true">→</span>
            </a>
        </div>
    </section>
</div>
