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

    // Only the two filters are state; the result set is derived on each render,
    // which keeps it out of the component payload sent to the browser.
    public function render()
    {
        $projects = Project::ordered()
            ->select(['id', 'title', 'description', 'image', 'tech_stack', 'demo_url', 'repo_url', 'is_featured'])
            ->when($this->filter === 'featured', fn($q) => $q->featured())
            ->when($this->tech !== '', fn($q) => $q->whereJsonContains('tech_stack', $this->tech))
            ->get()
            ->toArray();

        $techs = Project::ordered()->pluck('tech_stack')->filter()->flatten()->unique()->sort()->values()->toArray();

        return $this->view(['projects' => $projects, 'techs' => $techs])->layout('layouts.landing', [
            'description' => 'A selection of things I have built, shipped and learned from.',
        ]);
    }
}; ?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10 md:py-16">
    <div class="mb-8 md:mb-12 text-center">
        <flux:heading size="xl" class="mb-3">Projects</flux:heading>
        <flux:subheading class="max-w-xl mx-auto">Things I've built, shipped, and learned from.</flux:subheading>
    </div>

    {{-- Filter --}}
    <div class="flex flex-wrap justify-center items-center gap-3 mb-10">
        <flux:radio.group wire:model.live="filter" variant="segmented">
            <flux:radio value="all">All</flux:radio>
            <flux:radio value="featured">Featured</flux:radio>
        </flux:radio.group>

        @if (count($techs) > 0)
            <flux:select wire:model.live="tech" class="max-w-48">
                <option value="">Any technology</option>
                @foreach ($techs as $techOption)
                    <option value="{{ $techOption }}">{{ $techOption }}</option>
                @endforeach
            </flux:select>
        @endif
    </div>

    @if (count($projects) === 0)
        <div class="text-center py-20 text-zinc-400">
            <flux:icon name="folder-open" class="size-12 mx-auto mb-4 opacity-40" />
            <p>{{ $filter === 'all' && $tech === '' ? 'No projects yet.' : 'No projects match this filter.' }}</p>
        </div>
    @else
        <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-6">
            @foreach ($projects as $project)
                <div
                    class="group bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden hover:border-blue-400 dark:hover:border-blue-500 transition flex flex-col">
                    @if ($project['image'])
                        <img src="{{ Storage::disk('public')->url($project['image']) }}" alt="{{ $project['title'] }}"
                            class="w-full h-44 object-cover group-hover:scale-105 transition duration-300">
                    @else
                        <div
                            class="w-full h-44 bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                            <flux:icon name="code-bracket" class="size-12 text-white/50" />
                        </div>
                    @endif

                    <div class="p-5 flex flex-col flex-1">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <h2 class="font-semibold text-base">{{ $project['title'] }}</h2>
                            @if ($project['is_featured'])
                                <flux:badge color="amber" size="sm">Featured</flux:badge>
                            @endif
                        </div>

                        @php
                            $desc = $project['description'] ?? '';
                            $limit = 120;
                        @endphp
                        @if (strlen($desc) > $limit)
                            <div x-data="{ expanded: false }" class="text-sm text-zinc-500 dark:text-zinc-400 flex-1 mb-4">
                                <span x-show="!expanded">{{ Str::limit($desc, $limit) }}</span>
                                <span x-show="expanded" x-cloak>{{ $desc }}</span>
                                <button @click="expanded = !expanded"
                                    class="ml-1 text-blue-500 hover:text-blue-600 dark:text-blue-400 font-medium whitespace-nowrap">
                                    <span x-show="!expanded">Read more</span>
                                    <span x-show="expanded" x-cloak>Read less</span>
                                </button>
                            </div>
                        @else
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 flex-1 mb-4">{{ $desc }}</p>
                        @endif

                        @if (!empty($project['tech_stack']))
                            <div class="flex flex-wrap gap-1.5 mb-4">
                                @foreach ($project['tech_stack'] as $tech)
                                    <span
                                        class="text-xs bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 px-2 py-0.5 rounded-full">{{ $tech }}</span>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex gap-3 mt-auto">
                            @if ($project['demo_url'])
                                <a href="{{ $project['demo_url'] }}" target="_blank" rel="noopener"
                                    class="flex items-center gap-1.5 text-sm text-blue-600 dark:text-blue-400 hover:underline">
                                    <flux:icon name="arrow-top-right-on-square" class="size-3.5" /> Live Demo
                                </a>
                            @endif
                            @if ($project['repo_url'])
                                <a href="{{ $project['repo_url'] }}" target="_blank" rel="noopener"
                                    class="flex items-center gap-1.5 text-sm text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-100 transition">
                                    <flux:icon name="code-bracket" class="size-3.5" /> Source
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
