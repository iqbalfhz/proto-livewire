<?php

use App\Models\Post;
use App\Models\Project;
use App\Models\Skill;
use App\Models\SiteContent;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Home')] class extends Component {
    // Nothing on this page is interactive, so none of it is component state:
    // public properties would be serialised into the page and shipped back and
    // forth on every request. View data is rendered once and forgotten.
    public function render()
    {
        $hero = SiteContent::group('home');
        $about = SiteContent::group('about');

        return $this->view([
            'hero' => $hero,
            'about' => $about,
            'featuredProjects' => Project::ordered()
                ->featured()
                ->select(['id', 'title', 'slug', 'description', 'image', 'tech_stack', 'is_featured'])
                ->limit(6)
                ->get()
                ->toArray(),
            'latestPosts' => Post::published()
                ->select(['id', 'title', 'slug', 'excerpt', 'thumbnail', 'published_at'])
                ->limit(4)
                ->get()
                ->toArray(),
            'skills' => Skill::ordered()
                ->select(['id', 'name', 'category', 'level'])
                ->limit(12)
                ->get()
                ->toArray(),
        ])->layout('layouts.landing', [
            'description' => $hero['subheadline'] ?? ($about['bio'] ?? null),
        ]);
    }
}; ?>

<div>
    {{-- ═══════════════════════════ HERO ═══════════════════════════ --}}
    <section class="border-b border-rule">
        <div class="mx-auto max-w-6xl px-6">
            {{-- Dateline --}}
            <div class="flex items-center gap-4 border-b border-rule py-4">
                <span class="flex items-center gap-2.5">
                    <span class="size-1.5 animate-pulse rounded-full bg-oxblood"></span>
                    <span class="label">{{ $hero['badge'] ?? 'Available for work' }}</span>
                </span>
                <span class="h-px flex-1 bg-rule"></span>
                <span class="label hidden sm:block">Est. {{ date('Y') }}</span>
            </div>

            <div class="grid gap-12 py-16 md:py-24 lg:grid-cols-12 lg:gap-16">
                {{-- Headline --}}
                <div class="lg:col-span-8">
                    <h1 class="hero-headline display display-xl mb-8" data-reveal>
                        {!! $hero['headline'] ?? 'Hi, I\'m a <span>Developer</span>' !!}
                    </h1>

                    <p class="max-w-xl text-lg leading-relaxed text-ink-soft" data-reveal data-reveal-delay="60">
                        {{ $hero['subheadline'] ?? 'I build modern web applications with a focus on clean code, great UX, and solid performance.' }}
                    </p>

                    <div class="mt-10 flex flex-wrap gap-4" data-reveal data-reveal-delay="120">
                        <a href="{{ route('landing.projects') }}" wire:navigate class="btn-ink">
                            {{ $hero['cta_primary'] ?? 'View Projects' }}
                            <span aria-hidden="true">→</span>
                        </a>
                        <a href="{{ route('landing.contact') }}" wire:navigate class="btn-outline">
                            {{ $hero['cta_secondary'] ?? 'Get In Touch' }}
                        </a>
                    </div>
                </div>

                {{-- Portrait, hung like a plate in a book --}}
                <div class="lg:col-span-4" data-reveal data-reveal-delay="180">
                    <figure class="relative">
                        @if ($about['photo'] ?? null)
                            <img src="{{ Storage::disk('public')->url($about['photo']) }}"
                                alt="{{ $about['name'] ?? 'Portrait' }}"
                                class="aspect-4/5 w-full border border-rule-strong object-cover grayscale transition-all duration-700 hover:grayscale-0">
                        @else
                            <div
                                class="flex aspect-4/5 w-full items-center justify-center border border-rule-strong bg-paper-sunk">
                                <span class="display display-lg text-rule-strong">
                                    {{ Str::of($about['name'] ?? 'D')->substr(0, 1)->upper() }}
                                </span>
                            </div>
                        @endif
                        <figcaption class="mt-3 flex items-baseline justify-between border-t border-rule pt-3">
                            <span class="label">{{ $about['name'] ?? config('app.name') }}</span>
                            <span class="label">{{ $about['role'] ?? 'Developer' }}</span>
                        </figcaption>
                    </figure>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ SELECTED WORK ═══════════════════════ --}}
    @if (count($featuredProjects) > 0)
        <section class="mx-auto max-w-6xl px-6 py-20 md:py-28">
            <div class="mb-12 flex items-end justify-between gap-6 border-b border-rule-strong pb-5" data-reveal>
                <div class="flex items-baseline gap-4">
                    <span class="index-number">01</span>
                    <h2 class="display display-md">Selected Work</h2>
                </div>
                <a href="{{ route('landing.projects') }}" wire:navigate
                    class="label link-sweep shrink-0 hover:text-ink! transition-colors">
                    All work →
                </a>
            </div>

            <div>
                @foreach ($featuredProjects as $i => $project)
                    <a href="{{ route('landing.projects.show', $project['slug']) }}" wire:navigate
                        class="index-row group flex items-baseline gap-5 py-6 sm:gap-8 sm:py-8" data-reveal
                        data-reveal-delay="{{ $i * 70 }}"
                        @if ($project['image']) data-preview="{{ Storage::disk('public')->url($project['image']) }}" @endif>
                        <span class="index-number shrink-0">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>

                        <span class="index-title min-w-0 flex-1">
                            <span
                                class="display display-sm block truncate transition-colors duration-300 group-hover:text-oxblood">
                                {{ $project['title'] }}
                            </span>
                            @if (!empty($project['tech_stack']))
                                <span class="label mt-1.5 block sm:hidden">
                                    {{ implode(' · ', array_slice($project['tech_stack'], 0, 3)) }}
                                </span>
                            @endif
                        </span>

                        @if (!empty($project['tech_stack']))
                            <span class="label hidden shrink-0 sm:block">
                                {{ implode(' · ', array_slice($project['tech_stack'], 0, 3)) }}
                            </span>
                        @endif

                        <span
                            class="shrink-0 text-ink-muted transition-all duration-300 group-hover:translate-x-1 group-hover:text-oxblood"
                            aria-hidden="true">↗</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ═════════════════════════ WRITING ═════════════════════════ --}}
    @if (count($latestPosts) > 0)
        <section class="border-y border-rule bg-paper-sunk">
            <div class="mx-auto max-w-6xl px-6 py-20 md:py-28">
                <div class="mb-12 flex items-end justify-between gap-6 border-b border-rule-strong pb-5" data-reveal>
                    <div class="flex items-baseline gap-4">
                        <span class="index-number">02</span>
                        <h2 class="display display-md">Writing</h2>
                    </div>
                    <a href="{{ route('landing.blog') }}" wire:navigate
                        class="label link-sweep shrink-0 hover:text-ink! transition-colors">
                        All writing →
                    </a>
                </div>

                <div class="grid gap-x-12 gap-y-10 md:grid-cols-2">
                    @foreach ($latestPosts as $i => $post)
                        <a href="{{ route('landing.blog.show', $post['slug']) }}" wire:navigate
                            class="group block border-t border-rule pt-5" data-reveal data-reveal-delay="{{ $i * 80 }}">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="label">
                                    {{ $post['published_at'] ? \Carbon\Carbon::parse($post['published_at'])->format('d M Y') : 'Draft' }}
                                </span>
                                <span class="label transition-colors group-hover:text-oxblood">Read →</span>
                            </div>
                            <h3
                                class="display display-sm mb-2 transition-colors duration-300 group-hover:text-oxblood">
                                {{ $post['title'] }}
                            </h3>
                            @if ($post['excerpt'])
                                <p class="text-sm leading-relaxed text-ink-muted">
                                    {{ Str::limit($post['excerpt'], 120) }}
                                </p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ══════════════════════════ CRAFT ══════════════════════════ --}}
    @if (count($skills) > 0)
        <section class="mx-auto max-w-6xl px-6 py-20 md:py-28">
            <div class="mb-12 flex items-end justify-between gap-6 border-b border-rule-strong pb-5" data-reveal>
                <div class="flex items-baseline gap-4">
                    <span class="index-number">03</span>
                    <h2 class="display display-md">Craft</h2>
                </div>
                <a href="{{ route('landing.skills') }}" wire:navigate
                    class="label link-sweep shrink-0 hover:text-ink! transition-colors">
                    Full list →
                </a>
            </div>

            {{-- Type specimen: the tools set as display type --}}
            <div class="flex flex-wrap items-baseline gap-x-8 gap-y-4" data-reveal>
                @foreach ($skills as $skill)
                    <span
                        class="display display-sm text-ink-muted transition-colors duration-300 hover:text-oxblood">
                        {{ $skill['name'] }}
                    </span>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ═══════════════════════════ CTA ═══════════════════════════ --}}
    <section class="border-t border-rule-strong">
        <div class="mx-auto max-w-6xl px-6 py-24 text-center md:py-32">
            <p class="label mb-6" data-reveal>{{ $hero['badge'] ?? 'Available for work' }}</p>
            <p class="display display-lg mx-auto mb-10 max-w-3xl" data-reveal data-reveal-delay="100">
                {{ $hero['cta_section_text'] ?? 'Have a project in mind? I\'d love to help you bring it to life.' }}
            </p>
            <div data-reveal data-reveal-delay="200">
                <a href="{{ route('landing.contact') }}" wire:navigate class="btn-ink">
                    Get in touch <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </section>
</div>
