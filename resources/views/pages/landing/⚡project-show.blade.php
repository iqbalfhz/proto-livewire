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

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-10 md:py-16">
    {{-- Back link --}}
    <a href="{{ route('landing.projects') }}" wire:navigate
        class="inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-100 mb-8 transition">
        <flux:icon name="arrow-left" class="size-4" />
        Back to Projects
    </a>

    {{-- Hero image --}}
    @if ($project->image)
        <img src="{{ Storage::disk('public')->url($project->image) }}" alt="{{ $project->title }}"
            class="w-full h-64 sm:h-80 object-cover rounded-2xl mb-8">
    @else
        <div
            class="w-full h-48 sm:h-64 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mb-8">
            <flux:icon name="code-bracket" class="size-16 text-white/50" />
        </div>
    @endif

    {{-- Title --}}
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <h1 class="text-3xl md:text-4xl font-bold tracking-tight">{{ $project->title }}</h1>
        @if ($project->is_featured)
            <flux:badge color="amber" size="sm">Featured</flux:badge>
        @endif
    </div>

    {{-- Tech stack --}}
    @if (!empty($project->tech_stack))
        <div class="flex flex-wrap gap-2 mb-8">
            @foreach ($project->tech_stack as $tech)
                <a href="{{ route('landing.projects', ['tech' => $tech]) }}" wire:navigate
                    class="text-xs bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 hover:text-blue-600 dark:hover:text-blue-400 px-3 py-1 rounded-full transition">
                    {{ $tech }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- Links --}}
    @if ($project->demo_url || $project->repo_url)
        <div class="flex flex-wrap gap-3 mb-10">
            @if ($project->demo_url)
                <a href="{{ $project->demo_url }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition">
                    <flux:icon name="arrow-top-right-on-square" class="size-4" /> Live Demo
                </a>
            @endif
            @if ($project->repo_url)
                <a href="{{ $project->repo_url }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 border border-zinc-300 dark:border-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 text-sm font-semibold px-5 py-2.5 rounded-xl transition">
                    <flux:icon name="code-bracket" class="size-4" /> Source Code
                </a>
            @endif
        </div>
    @endif

    {{-- Description, in full --}}
    @if ($project->description)
        <div class="prose prose-zinc dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300 leading-relaxed">
            {!! nl2br(e($project->description)) !!}
        </div>
    @endif

    {{-- More projects --}}
    @if (count($moreProjects) > 0)
        <div class="mt-16 pt-10 border-t border-zinc-200 dark:border-zinc-700">
            <flux:heading class="mb-6">More Projects</flux:heading>
            <div class="grid sm:grid-cols-3 gap-4">
                @foreach ($moreProjects as $other)
                    <a href="{{ route('landing.projects.show', $other['slug']) }}" wire:navigate
                        class="group block bg-zinc-50 dark:bg-zinc-800 rounded-xl overflow-hidden hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                        @if ($other['image'])
                            <img src="{{ Storage::disk('public')->url($other['image']) }}" alt="{{ $other['title'] }}"
                                class="w-full h-24 object-cover">
                        @endif
                        <div class="p-4">
                            <h3
                                class="text-sm font-medium group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                                {{ $other['title'] }}</h3>
                            @if (!empty($other['tech_stack']))
                                <p class="text-xs text-zinc-400 mt-1">{{ implode(' · ', array_slice($other['tech_stack'], 0, 3)) }}
                                </p>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
