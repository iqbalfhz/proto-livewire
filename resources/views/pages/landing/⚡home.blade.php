<?php

use App\Models\Post;
use App\Models\Project;
use App\Models\SiteContent;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Home')] class extends Component {
    public array $hero = [];
    public array $featuredProjects = [];
    public array $latestPosts = [];

    public function mount(): void
    {
        $this->hero = SiteContent::group('home');
        $this->featuredProjects = Project::ordered()->featured()->limit(3)->get()->toArray();
        $this->latestPosts = Post::published()->limit(3)->get()->toArray();
    }

    public function render()
    {
        return view('pages.landing.home')->layout('layouts.landing');
    }
}; ?>

<div>
    {{-- Hero Section --}}
    <section
        class="relative overflow-hidden bg-gradient-to-br from-zinc-50 to-zinc-100 dark:from-zinc-900 dark:to-zinc-800 py-24 md:py-36">
        <div class="max-w-5xl mx-auto px-6 text-center">
            <div
                class="inline-flex items-center gap-2 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-sm font-medium px-4 py-1.5 rounded-full mb-6">
                <span class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></span>
                {{ $hero['badge'] ?? 'Available for work' }}
            </div>

            <h1 class="text-4xl md:text-6xl font-bold tracking-tight mb-6 leading-tight">
                {!! $hero['headline'] ?? 'Hi, I\'m a <span class="text-blue-600 dark:text-blue-400">Full Stack Developer</span>' !!}
            </h1>

            <p class="text-lg md:text-xl text-zinc-500 dark:text-zinc-400 max-w-2xl mx-auto mb-10">
                {{ $hero['subheadline'] ?? 'I build modern web applications with Laravel, Livewire & Tailwind CSS.' }}
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <flux:button :href="route('landing.projects')" variant="primary" size="lg" wire:navigate>
                    {{ $hero['cta_primary'] ?? 'View Projects' }}
                </flux:button>
                <flux:button :href="route('landing.contact')" variant="outline" size="lg" wire:navigate>
                    {{ $hero['cta_secondary'] ?? 'Get In Touch' }}
                </flux:button>
            </div>
        </div>

        {{-- Decorative blobs --}}
        <div
            class="absolute -top-24 -right-24 w-96 h-96 bg-blue-400/10 dark:bg-blue-500/10 rounded-full blur-3xl pointer-events-none">
        </div>
        <div
            class="absolute -bottom-24 -left-24 w-96 h-96 bg-purple-400/10 dark:bg-purple-500/10 rounded-full blur-3xl pointer-events-none">
        </div>
    </section>

    {{-- Featured Projects --}}
    @if (count($featuredProjects) > 0)
        <section class="max-w-5xl mx-auto px-6 py-20">
            <div class="flex items-end justify-between mb-10">
                <div>
                    <flux:heading size="xl">Featured Projects</flux:heading>
                    <flux:subheading>A selection of my recent work</flux:subheading>
                </div>
                <flux:button :href="route('landing.projects')" variant="ghost" wire:navigate>
                    View all →
                </flux:button>
            </div>

            <div class="grid md:grid-cols-3 gap-6">
                @foreach ($featuredProjects as $project)
                    <a href="{{ route('landing.projects') }}" wire:navigate
                        class="group block rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden hover:border-blue-400 dark:hover:border-blue-500 transition">
                        @if ($project['image'])
                            <img src="{{ Storage::url($project['image']) }}" alt="{{ $project['title'] }}"
                                class="w-full h-44 object-cover group-hover:scale-105 transition duration-300">
                        @else
                            <div
                                class="w-full h-44 bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                                <flux:icon name="code-bracket" class="size-12 text-white/50" />
                            </div>
                        @endif
                        <div class="p-5">
                            <h3
                                class="font-semibold text-base mb-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                                {{ $project['title'] }}</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2">
                                {{ $project['description'] }}</p>
                            @if (!empty($project['tech_stack']))
                                <div class="flex flex-wrap gap-1.5 mt-3">
                                    @foreach (array_slice($project['tech_stack'], 0, 3) as $tech)
                                        <span
                                            class="text-xs bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 px-2 py-0.5 rounded-full">{{ $tech }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Latest Blog Posts --}}
    @if (count($latestPosts) > 0)
        <section class="bg-zinc-50 dark:bg-zinc-800/50 py-20">
            <div class="max-w-5xl mx-auto px-6">
                <div class="flex items-end justify-between mb-10">
                    <div>
                        <flux:heading size="xl">Latest Posts</flux:heading>
                        <flux:subheading>Thoughts, tutorials & notes</flux:subheading>
                    </div>
                    <flux:button :href="route('landing.blog')" variant="ghost" wire:navigate>
                        View all →
                    </flux:button>
                </div>

                <div class="grid md:grid-cols-3 gap-6">
                    @foreach ($latestPosts as $post)
                        <a href="{{ route('landing.blog.show', $post['slug']) }}" wire:navigate
                            class="group block bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 p-6 hover:border-blue-400 dark:hover:border-blue-500 transition">
                            @if ($post['thumbnail'])
                                <img src="{{ Storage::url($post['thumbnail']) }}" alt="{{ $post['title'] }}"
                                    class="w-full h-36 object-cover rounded-lg mb-4">
                            @endif
                            <p class="text-xs text-zinc-400 mb-2">
                                {{ \Carbon\Carbon::parse($post['published_at'])->format('M d, Y') }}</p>
                            <h3
                                class="font-semibold group-hover:text-blue-600 dark:group-hover:text-blue-400 transition mb-2">
                                {{ $post['title'] }}</h3>
                            <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-3">{{ $post['excerpt'] }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- CTA Section --}}
    <section class="max-w-5xl mx-auto px-6 py-20 text-center">
        <flux:heading size="xl" class="mb-4">Let's Work Together</flux:heading>
        <flux:subheading class="mb-8 max-w-lg mx-auto">
            {{ $hero['cta_section_text'] ?? "Have a project in mind? I'd love to hear about it." }}
        </flux:subheading>
        <flux:button :href="route('landing.contact')" variant="primary" size="lg" wire:navigate>
            Get In Touch
        </flux:button>
    </section>
</div>
