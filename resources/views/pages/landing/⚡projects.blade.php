<?php

use App\Models\Project;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Projects')] class extends Component {
    #[Url]
    public string $filter = 'all';

    #[Url]
    public string $tech = '';

    public function clearFilters(): void
    {
        $this->reset('filter', 'tech');
    }

    // Only the two filters are state; the result set is derived on each render,
    // which keeps it out of the component payload sent to the browser.
    public function render()
    {
        $projects = Project::ordered()
            ->select(['id', 'title', 'slug', 'description', 'image', 'tech_stack', 'demo_url', 'repo_url', 'is_featured'])
            ->when($this->filter === 'featured', fn($q) => $q->featured())
            ->when($this->tech !== '', fn($q) => $q->whereJsonContains('tech_stack', $this->tech))
            ->get()
            ->toArray();

        // Most-used first: the filters people actually reach for lead the row,
        // and the long tail hides behind a toggle rather than filling the page.
        $techs = Project::ordered()
            ->pluck('tech_stack')
            ->filter()
            ->flatten()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->values()
            ->toArray();

        return $this->view(['projects' => $projects, 'techs' => $techs])->layout('layouts.landing', [
            'description' => 'A selection of things I have built, shipped and learned from.',
        ]);
    }
}; ?>

<div>
    {{-- ═══════════════════════ MASTHEAD ═══════════════════════ --}}
    <section class="border-b border-rule">
        <div class="mx-auto max-w-6xl px-6">
            <div class="flex items-center gap-4 border-b border-rule py-4">
                <span class="label">All projects</span>
                <span class="h-px flex-1 bg-rule"></span>
                <span class="label">{{ count($projects) }} {{ Str::plural('entry', count($projects)) }}</span>
            </div>

            <div class="grid items-end gap-8 py-14 md:grid-cols-12 md:py-20">
                <h1 class="display display-xl md:col-span-7" data-reveal>Projects</h1>
                <p class="text-lg leading-relaxed text-ink-soft md:col-span-5 md:pb-3" data-reveal
                    data-reveal-delay="120">
                    Things I've built, shipped, and learned from — from client work to
                    experiments that never left the workshop.
                </p>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ FILTERS ═══════════════════════ --}}
    <section class="sticky top-[57px] z-30 border-b border-rule bg-paper/90 backdrop-blur-md md:top-[105px]">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-3 px-6 py-4">
            <span class="label shrink-0 text-ink!">Filter</span>

            <button type="button" wire:click="$set('filter', 'all')"
                class="label link-sweep {{ $filter === 'all' ? 'link-retract text-ink!' : '' }} hover:text-ink! transition-colors">
                All
            </button>
            <button type="button" wire:click="$set('filter', 'featured')"
                class="label link-sweep {{ $filter === 'featured' ? 'link-retract text-ink!' : '' }} hover:text-ink! transition-colors">
                Featured
            </button>

            @if (count($techs) > 0)
                @php($visibleCount = 8)
                <span class="hidden h-4 w-px bg-rule-strong sm:block"></span>

                <div class="flex flex-wrap items-center gap-x-6 gap-y-3" x-data="{ showAll: false }">
                    <button type="button" wire:click="$set('tech', '')"
                        class="label link-sweep {{ $tech === '' ? 'link-retract text-ink!' : '' }} hover:text-ink! transition-colors">
                        Any tech
                    </button>

                    @foreach ($techs as $i => $techOption)
                        <button type="button" wire:click="$set('tech', @js($techOption))"
                            @if ($i >= $visibleCount && $tech !== $techOption) x-cloak x-show="showAll" @endif
                            class="label link-sweep {{ $tech === $techOption ? 'link-retract text-ink!' : '' }} hover:text-ink! transition-colors">
                            {{ $techOption }}
                        </button>
                    @endforeach

                    @if (count($techs) > $visibleCount)
                        <button type="button" x-on:click="showAll = !showAll"
                            class="label link-sweep text-oxblood! hover:opacity-70 transition-opacity">
                            <span x-show="!showAll">+{{ count($techs) - $visibleCount }} more</span>
                            <span x-cloak x-show="showAll">Show less</span>
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </section>

    {{-- ═══════════════════════ THE INDEX ═══════════════════════ --}}
    <section class="mx-auto max-w-6xl px-6 py-14 md:py-20">
        @if (count($projects) === 0)
            <div class="border-y border-rule py-28 text-center" data-reveal>
                <p class="display display-md mb-3 text-ink-muted">
                    {{ $filter === 'all' && $tech === '' ? 'No projects yet.' : 'No projects match this filter.' }}
                </p>
                @if ($filter !== 'all' || $tech !== '')
                    <button type="button" wire:click="clearFilters"
                        class="label link-sweep link-retract hover:text-ink! transition-colors">
                        Clear filters
                    </button>
                @endif
            </div>
        @else
            <div class="border-t border-rule-strong">
                @foreach ($projects as $i => $project)
                    <a href="{{ route('landing.projects.show', $project['slug']) }}" wire:navigate
                        class="index-row group flex items-baseline gap-5 py-7 sm:gap-8 sm:py-9" data-reveal
                        data-reveal-delay="{{ min($i, 6) * 60 }}"
                        @if ($project['image']) data-preview="{{ Storage::disk('public')->url($project['image']) }}" @endif>
                        <span class="index-number shrink-0">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>

                        <span class="index-title min-w-0 flex-1">
                            <span class="flex items-baseline gap-3">
                                <span
                                    class="display display-sm truncate transition-colors duration-300 group-hover:text-oxblood">
                                    {{ $project['title'] }}
                                </span>
                                @if ($project['is_featured'])
                                    <span class="label shrink-0 text-oxblood!">★</span>
                                @endif
                            </span>

                            @if ($project['description'])
                                <span class="mt-2 block max-w-xl text-sm leading-relaxed text-ink-muted">
                                    {{ Str::limit($project['description'], 130) }}
                                </span>
                            @endif

                            @if (!empty($project['tech_stack']))
                                <span class="label mt-2 block lg:hidden">
                                    {{ implode(' · ', array_slice($project['tech_stack'], 0, 4)) }}
                                </span>
                            @endif
                        </span>

                        @if (!empty($project['tech_stack']))
                            <span class="label hidden w-60 shrink-0 truncate text-right lg:block">
                                {{ implode(' · ', array_slice($project['tech_stack'], 0, 3)) }}
                            </span>
                        @endif

                        <span
                            class="shrink-0 text-ink-muted transition-all duration-300 group-hover:translate-x-1 group-hover:text-oxblood"
                            aria-hidden="true">↗</span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</div>
