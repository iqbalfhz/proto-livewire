<?php

use App\Models\Project;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Projects')] class extends Component {
    #[Url]
    public string $filter = 'all';

    public array $projects = [];
    public array $techs = [];

    public function mount(): void
    {
        $this->loadProjects();
        $all = Project::ordered()->pluck('tech_stack')->filter()->flatten()->unique()->sort()->values()->toArray();
        $this->techs = $all;
    }

    public function updatedFilter(): void
    {
        $this->loadProjects();
    }

    private function loadProjects(): void
    {
        $this->projects = Project::ordered()
            ->when($this->filter === 'featured', fn($q) => $q->featured())
            ->get()
            ->toArray();
    }

    public function render()
    {
        return $this->view()->layout('layouts.landing');
    }
}; ?>

<div class="max-w-5xl mx-auto px-6 py-16">
    <div class="mb-12 text-center">
        <flux:heading size="xl" class="mb-3">Projects</flux:heading>
        <flux:subheading class="max-w-xl mx-auto">Things I've built, shipped, and learned from.</flux:subheading>
    </div>

    {{-- Filter --}}
    <div class="flex justify-center mb-10">
        <flux:radio.group wire:model.live="filter" variant="segmented">
            <flux:radio value="all">All</flux:radio>
            <flux:radio value="featured">Featured</flux:radio>
        </flux:radio.group>
    </div>

    @if (count($projects) === 0)
        <div class="text-center py-20 text-zinc-400">
            <flux:icon name="folder-open" class="size-12 mx-auto mb-4 opacity-40" />
            <p>No projects yet.</p>
        </div>
    @else
        <div class="grid md:grid-cols-3 gap-6">
            @foreach ($projects as $project)
                <div
                    class="group bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden hover:border-blue-400 dark:hover:border-blue-500 transition flex flex-col">
                    @if ($project['image'])
                        <img src="{{ Storage::url($project['image']) }}" alt="{{ $project['title'] }}"
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

                        <p class="text-sm text-zinc-500 dark:text-zinc-400 flex-1 mb-4">{{ $project['description'] }}
                        </p>

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
