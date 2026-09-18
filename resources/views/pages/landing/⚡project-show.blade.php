<?php

use App\Models\Project;
use App\Models\SiteContent;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

new class extends Component {
    public Project $project;

    public function mount(string $slug): void
    {
        $this->project = Project::where('slug', $slug)->firstOrFail();
    }

    public function render()
    {
        $moreProjects = Project::ordered()
            ->select(['id', 'title', 'slug', 'image', 'tech_stack'])
            ->whereKeyNot($this->project->getKey())
            ->limit(3)
            ->get()
            ->toArray();

        $url = route('landing.projects.show', $this->project->slug);
        $image = $this->project->image ? Storage::disk('public')->url($this->project->image) : null;

        return $this->view(['moreProjects' => $moreProjects])
            ->title($this->project->title)
            ->layout('layouts.landing', [
                'description' => $this->project->description,
                'ogImage' => $image,
                'jsonLd' => array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'CreativeWork',
                    'name' => $this->project->title,
                    'description' => $this->project->description,
                    'url' => $url,
                    'image' => $image,
                    'keywords' => implode(', ', (array) $this->project->tech_stack),
                    'author' => [
                        '@type' => 'Person',
                        'name' => SiteContent::get('about', 'name', config('app.name')),
                    ],
                ]),
            ]);
    }
}; ?>

<div>
    {{-- ═══════════════════════ HEADER ═══════════════════════ --}}
    <section class="border-b border-rule">
        <div class="mx-auto max-w-5xl px-6">
            <div class="flex items-center gap-4 border-b border-rule py-4">
                <a href="{{ route('landing.projects') }}" wire:navigate
                    class="label link-sweep shrink-0 hover:text-ink! transition-colors">
                    ← All projects
                </a>
                <span class="h-px flex-1 bg-rule"></span>
                @if ($project->is_featured)
                    <span class="label text-oxblood!">★ Featured</span>
                @endif
            </div>

            <div class="py-14 md:py-20">
                <h1 class="display display-lg mb-8 max-w-4xl" data-reveal>{{ $project->title }}</h1>

                {{-- Specimen table: the facts, set like a colophon --}}
                <dl class="grid gap-x-10 gap-y-5 border-t border-rule pt-6 sm:grid-cols-3" data-reveal
                    data-reveal-delay="100">
                    @if (!empty($project->tech_stack))
                        <div class="sm:col-span-2">
                            <dt class="label mb-2.5">Built with</dt>
                            <dd class="flex flex-wrap gap-x-4 gap-y-1.5">
                                @foreach ($project->tech_stack as $tech)
                                    <a href="{{ route('landing.projects', ['tech' => $tech]) }}" wire:navigate
                                        class="link-sweep text-sm text-ink-soft transition-colors hover:text-oxblood">
                                        {{ $tech }}
                                    </a>
                                @endforeach
                            </dd>
                        </div>
                    @endif

                    @if ($project->demo_url || $project->repo_url)
                        <div>
                            <dt class="label mb-2.5">Links</dt>
                            <dd class="flex flex-col gap-1.5">
                                @if ($project->demo_url)
                                    <a href="{{ $project->demo_url }}" target="_blank" rel="noopener"
                                        class="link-sweep text-sm text-ink-soft transition-colors hover:text-oxblood">
                                        Live demo ↗
                                    </a>
                                @endif
                                @if ($project->repo_url)
                                    <a href="{{ $project->repo_url }}" target="_blank" rel="noopener"
                                        class="link-sweep text-sm text-ink-soft transition-colors hover:text-oxblood">
                                        Source code ↗
                                    </a>
                                @endif
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ PLATE ═══════════════════════ --}}
    <section class="border-b border-rule bg-paper-sunk">
        <div class="mx-auto max-w-5xl px-6 py-12 md:py-16">
            <figure data-reveal>
                @if ($project->image)
                    <img src="{{ Storage::disk('public')->url($project->image) }}" alt="{{ $project->title }}"
                        class="w-full border border-rule-strong object-cover">
                @else
                    <div class="flex aspect-16/9 w-full items-center justify-center border border-rule-strong bg-paper">
                        <span class="display display-xl text-rule-strong">
                            {{ Str::of($project->title)->substr(0, 1)->upper() }}
                        </span>
                    </div>
                @endif
                <figcaption class="label mt-3 border-t border-rule pt-3">
                    {{ $project->title }}
                </figcaption>
            </figure>
        </div>
    </section>

    {{-- ═══════════════════════ THE WRITE-UP ═══════════════════════ --}}
    @if ($project->description)
        <section class="mx-auto max-w-5xl px-6 py-16 md:py-24">
            <div class="grid gap-10 md:grid-cols-12" data-reveal>
                <div class="md:col-span-3">
                    <p class="label md:sticky md:top-32">About this project</p>
                </div>
                <div class="dropcap text-lg leading-[1.8] text-ink-soft md:col-span-9">
                    {!! \App\Support\Text::paragraphs($project->description) !!}
                </div>
            </div>
        </section>
    @endif

    {{-- ═══════════════════════ MORE WORK ═══════════════════════ --}}
    @if (count($moreProjects) > 0)
        <section class="border-t border-rule-strong">
            <div class="mx-auto max-w-5xl px-6 py-16 md:py-20">
                <div class="mb-8 flex items-baseline gap-4" data-reveal>
                    <span class="index-number">→</span>
                    <h2 class="display display-md">Other Projects</h2>
                </div>

                <div class="border-t border-rule">
                    @foreach ($moreProjects as $i => $other)
                        <a href="{{ route('landing.projects.show', $other['slug']) }}" wire:navigate
                            class="index-row group flex items-baseline gap-5 py-6 sm:gap-8" data-reveal
                            data-reveal-delay="{{ $i * 70 }}"
                            @if ($other['image']) data-preview="{{ Storage::disk('public')->url($other['image']) }}" @endif>
                            <span class="index-number shrink-0">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="index-title min-w-0 flex-1">
                                <span
                                    class="display display-sm block truncate transition-colors duration-300 group-hover:text-oxblood">
                                    {{ $other['title'] }}
                                </span>
                            </span>
                            @if (!empty($other['tech_stack']))
                                <span class="label hidden shrink-0 sm:block">
                                    {{ implode(' · ', array_slice($other['tech_stack'], 0, 3)) }}
                                </span>
                            @endif
                            <span
                                class="shrink-0 text-ink-muted transition-all duration-300 group-hover:translate-x-1 group-hover:text-oxblood"
                                aria-hidden="true">↗</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
