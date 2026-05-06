<?php

use App\Models\Post;
use App\Models\Project;
use App\Models\Skill;
use App\Models\SiteContent;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Home')] class extends Component {
    public array $hero = [];
    public array $about = [];
    public array $featuredProjects = [];
    public array $latestPosts = [];
    public array $skills = [];

    public function mount(): void
    {
        $this->hero = SiteContent::group('home');
        $this->about = SiteContent::group('about');
        $this->featuredProjects = Project::ordered()->featured()->limit(6)->get()->toArray();
        $this->latestPosts = Post::published()->limit(6)->get()->toArray();
        $this->skills = Skill::ordered()->limit(8)->get()->toArray();
    }

    public function render()
    {
        return $this->view()->layout('layouts.landing');
    }
}; ?>

<div>
    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• HERO â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    <section class="relative min-h-[92vh] flex items-center overflow-hidden">
        {{-- Background --}}
        <div
            class="absolute inset-0 bg-gradient-to-br from-white via-blue-50/40 to-purple-50/30 dark:from-zinc-950 dark:via-blue-950/20 dark:to-purple-950/10">
        </div>
        {{-- Dot grid --}}
        <div class="absolute inset-0 opacity-[0.025] dark:opacity-[0.04]"
            style="background-image: radial-gradient(circle, #94a3b8 1px, transparent 1px); background-size: 28px 28px;">
        </div>

        <div class="relative max-w-6xl mx-auto px-6 py-24 w-full">
            <div class="grid lg:grid-cols-2 gap-14 items-center">
                {{-- Left: Text --}}
                <div>
                    <div
                        class="inline-flex items-center gap-2 bg-blue-500/10 border border-blue-500/20 text-blue-600 dark:text-blue-400 text-xs font-semibold uppercase tracking-wider px-4 py-2 rounded-full mb-7">
                        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-pulse"></span>
                        {{ $hero['badge'] ?? 'Available for work' }}
                    </div>

                    <h1 class="text-5xl md:text-6xl lg:text-[3.75rem] font-extrabold tracking-tight leading-[1.1] mb-6">
                        {!! $hero['headline'] ??
                            'Hi, I\'m a <span class="bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">Developer</span>' !!}
                    </h1>

                    <p class="text-lg text-zinc-500 dark:text-zinc-400 leading-relaxed mb-9 max-w-lg">
                        {{ $hero['subheadline'] ?? 'I build modern web applications with Laravel, Livewire & Tailwind CSS.' }}
                    </p>

                    <div class="flex flex-wrap gap-3 mb-10">
                        <a href="{{ route('landing.projects') }}" wire:navigate
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-6 py-3 rounded-xl transition shadow-lg shadow-blue-600/25">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            {{ $hero['cta_primary'] ?? 'View Projects' }}
                        </a>
                        <a href="{{ route('landing.contact') }}" wire:navigate
                            class="inline-flex items-center gap-2 bg-white dark:bg-zinc-800 hover:bg-zinc-50 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-100 text-sm font-semibold px-6 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 transition shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            {{ $hero['cta_secondary'] ?? 'Get In Touch' }}
                        </a>
                    </div>

                    {{-- Social Links --}}
                    <div class="flex items-center gap-4">
                        @if (!empty($about['github_url']))
                            <a href="{{ $about['github_url'] }}" target="_blank" rel="noopener"
                                class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path fill-rule="evenodd"
                                        d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.92.359.31.678.921.678 1.856 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                        @endif
                        @if (!empty($about['linkedin_url']))
                            <a href="{{ $about['linkedin_url'] }}" target="_blank" rel="noopener"
                                class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                                </svg>
                            </a>
                        @endif
                        @if (!empty($about['twitter_url']))
                            <a href="{{ $about['twitter_url'] }}" target="_blank" rel="noopener"
                                class="text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.742l7.737-8.835L1.254 2.25H8.08l4.259 5.63L18.244 2.25zm-1.161 17.52h1.833L7.084 4.126H5.117L17.083 19.77z" />
                                </svg>
                            </a>
                        @endif
                        <div class="h-4 w-px bg-zinc-300 dark:bg-zinc-700"></div>
                        <a href="{{ route('landing.about') }}" wire:navigate
                            class="text-sm text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 transition flex items-center gap-1">
                            More about me
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

                {{-- Right: Visual Card --}}
                <div class="hidden lg:flex justify-end items-center">
                    <div class="relative w-80">
                        {{-- Main card --}}
                        <div
                            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-3xl p-8 shadow-2xl shadow-zinc-200/60 dark:shadow-zinc-900">
                            {{-- Avatar --}}
                            <div class="flex items-center gap-4 mb-6">
                                @if (!empty($about['photo']))
                                    <img src="{{ Storage::url($about['photo']) }}" alt="{{ $about['name'] ?? '' }}"
                                        class="w-16 h-16 rounded-2xl object-cover ring-2 ring-blue-500/20">
                                @else
                                    <div
                                        class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl">
                                        {{ strtoupper(substr($about['name'] ?? 'D', 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <p class="font-semibold">{{ $about['name'] ?? 'Developer' }}</p>
                                    <p class="text-sm text-zinc-500">{{ $about['role'] ?? 'Full Stack Developer' }}</p>
                                </div>
                            </div>

                            {{-- Tech stack --}}
                            <p class="text-xs font-semibold text-zinc-400 uppercase tracking-wider mb-3">Tech Stack</p>
                            <div class="flex flex-wrap gap-2 mb-6">
                                @foreach (['Laravel', 'Livewire', 'Tailwind CSS', 'PHP', 'MySQL'] as $tech)
                                    <span
                                        class="text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 px-2.5 py-1 rounded-lg">{{ $tech }}</span>
                                @endforeach
                            </div>

                            {{-- Status --}}
                            <div
                                class="flex items-center gap-2 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl px-3 py-2">
                                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                                <span
                                    class="text-xs font-medium text-green-700 dark:text-green-400">{{ $hero['badge'] ?? 'Available for work' }}</span>
                            </div>
                        </div>

                        {{-- Floating badge: Projects --}}
                        <div
                            class="absolute -top-5 -right-5 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-xl px-4 py-3 text-center">
                            <p class="text-xl font-bold text-blue-600">{{ count($featuredProjects) }}+</p>
                            <p class="text-xs text-zinc-500">Projects</p>
                        </div>

                        {{-- Floating badge: Posts --}}
                        <div
                            class="absolute -bottom-5 -left-5 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-2xl shadow-xl px-4 py-3 text-center">
                            <p class="text-xl font-bold text-purple-600">{{ count($latestPosts) }}+</p>
                            <p class="text-xs text-zinc-500">Posts</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Blobs --}}
        <div
            class="absolute top-0 right-0 w-[700px] h-[700px] bg-blue-400/8 dark:bg-blue-500/8 rounded-full blur-3xl -translate-y-1/3 translate-x-1/4 pointer-events-none">
        </div>
        <div
            class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-purple-400/8 dark:bg-purple-500/8 rounded-full blur-3xl translate-y-1/3 -translate-x-1/4 pointer-events-none">
        </div>

        {{-- Scroll indicator --}}
        <div
            class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-1 text-zinc-400 animate-bounce">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </section>

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• FEATURED PROJECTS â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    @if (count($featuredProjects) > 0)
        <section class="py-24 bg-zinc-50 dark:bg-zinc-900/50">
            <div class="max-w-6xl mx-auto px-6">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-12">
                    <div>
                        <p class="text-xs font-semibold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-2">
                            Portfolio</p>
                        <h2 class="text-3xl md:text-4xl font-bold">Featured Projects</h2>
                        <p class="text-zinc-500 dark:text-zinc-400 mt-2">A selection of my recent work</p>
                    </div>
                    <a href="{{ route('landing.projects') }}" wire:navigate
                        class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 dark:text-blue-400 hover:gap-2.5 transition-all">
                        View all projects
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($featuredProjects as $project)
                        <a href="{{ route('landing.projects') }}" wire:navigate
                            class="group bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden hover:border-blue-400/50 dark:hover:border-blue-500/50 hover:shadow-xl hover:shadow-blue-500/10 transition-all duration-300">
                            {{-- Thumbnail --}}
                            @if ($project['image'])
                                <div class="overflow-hidden">
                                    <img src="{{ Storage::url($project['image']) }}" alt="{{ $project['title'] }}"
                                        class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-500">
                                </div>
                            @else
                                <div
                                    class="w-full h-48 bg-gradient-to-br from-blue-500 via-blue-600 to-purple-700 flex items-center justify-center relative overflow-hidden">
                                    <div class="absolute inset-0 opacity-10"
                                        style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 20px 20px;">
                                    </div>
                                    <svg class="w-14 h-14 text-white/60" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                    </svg>
                                </div>
                            @endif

                            <div class="p-6">
                                {{-- Featured badge --}}
                                @if ($project['is_featured'])
                                    <span
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 px-2 py-0.5 rounded-full mb-3">
                                        <svg class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        Featured
                                    </span>
                                @endif

                                <h3
                                    class="font-bold text-base mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                                    {{ $project['title'] }}
                                </h3>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-2 mb-4">
                                    {{ $project['description'] }}</p>

                                @if (!empty($project['tech_stack']))
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach (array_slice((array) $project['tech_stack'], 0, 3) as $tech)
                                            <span
                                                class="text-xs font-medium bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 px-2 py-0.5 rounded-lg">{{ $tech }}</span>
                                        @endforeach
                                        @if (count((array) $project['tech_stack']) > 3)
                                            <span
                                                class="text-xs text-zinc-400">+{{ count((array) $project['tech_stack']) - 3 }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="mt-12 text-center">
                    <a href="{{ route('landing.projects') }}" wire:navigate
                        class="inline-flex items-center gap-2 border-2 border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-400 dark:hover:text-zinc-900 font-semibold text-sm px-8 py-3.5 rounded-xl transition">
                        Lihat Semua Projects
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• LATEST POSTS â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    @if (count($latestPosts) > 0)
        <section class="py-24">
            <div class="max-w-6xl mx-auto px-6">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-12">
                    <div>
                        <p
                            class="text-xs font-semibold text-purple-600 dark:text-purple-400 uppercase tracking-wider mb-2">
                            Writing</p>
                        <h2 class="text-3xl md:text-4xl font-bold">Latest Posts</h2>
                        <p class="text-zinc-500 dark:text-zinc-400 mt-2">Thoughts, tutorials & notes</p>
                    </div>
                    <a href="{{ route('landing.blog') }}" wire:navigate
                        class="inline-flex items-center gap-1.5 text-sm font-medium text-purple-600 dark:text-purple-400 hover:gap-2.5 transition-all">
                        View all posts
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($latestPosts as $post)
                        <a href="{{ route('landing.blog.show', $post['slug']) }}" wire:navigate
                            class="group bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 overflow-hidden hover:border-purple-400/50 dark:hover:border-purple-500/50 hover:shadow-xl hover:shadow-purple-500/10 transition-all duration-300 flex flex-col">
                            @if ($post['thumbnail'])
                                <div class="overflow-hidden">
                                    <img src="{{ Storage::url($post['thumbnail']) }}" alt="{{ $post['title'] }}"
                                        class="w-full h-44 object-cover group-hover:scale-105 transition-transform duration-500">
                                </div>
                            @else
                                <div
                                    class="w-full h-44 bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center relative overflow-hidden">
                                    <div class="absolute inset-0 opacity-10"
                                        style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 20px 20px;">
                                    </div>
                                    <svg class="w-12 h-12 text-white/60" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            @endif

                            <div class="p-6 flex flex-col flex-1">
                                <p class="text-xs text-zinc-400 mb-3 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ \Carbon\Carbon::parse($post['published_at'])->format('M d, Y') }}
                                </p>
                                <h3
                                    class="font-bold text-base mb-2 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition leading-snug">
                                    {{ $post['title'] }}
                                </h3>
                                <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-3 flex-1">
                                    {{ $post['excerpt'] }}</p>
                                <div
                                    class="mt-4 flex items-center gap-1 text-xs font-semibold text-purple-600 dark:text-purple-400 group-hover:gap-2 transition-all">
                                    Read more
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="mt-12 text-center">
                    <a href="{{ route('landing.blog') }}" wire:navigate
                        class="inline-flex items-center gap-2 border-2 border-purple-600 text-purple-600 dark:border-purple-400 dark:text-purple-400 hover:bg-purple-600 hover:text-white dark:hover:bg-purple-400 dark:hover:text-zinc-900 font-semibold text-sm px-8 py-3.5 rounded-xl transition">
                        Lihat Semua Artikel
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• CTA â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• --}}
    {{-- ═══════════════════════════════ SKILLS ═══════════════════════════════ --}}
    @if (count($skills) > 0)
        <section class="py-24">
            <div class="max-w-6xl mx-auto px-6">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-12">
                    <div>
                        <p
                            class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-2">
                            Expertise</p>
                        <h2 class="text-3xl md:text-4xl font-bold">My Skills</h2>
                        <p class="text-zinc-500 dark:text-zinc-400 mt-2">Technologies & tools I work with</p>
                    </div>
                    <a href="{{ route('landing.skills') }}" wire:navigate
                        class="inline-flex items-center gap-1.5 text-sm font-medium text-emerald-600 dark:text-emerald-400 hover:gap-2.5 transition-all">
                        Lihat semua skills
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach ($skills as $skill)
                        <div
                            class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 p-5 hover:border-emerald-400/50 dark:hover:border-emerald-500/50 hover:shadow-lg hover:shadow-emerald-500/10 transition-all duration-300">
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <h3 class="font-semibold text-sm leading-snug">{{ $skill['name'] }}</h3>
                            </div>
                            @if ($skill['category'])
                                <span
                                    class="inline-block text-xs font-medium bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 px-2 py-0.5 rounded-full mb-3">
                                    {{ $skill['category'] }}
                                </span>
                            @endif
                            <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-1.5">
                                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-1.5 rounded-full"
                                    style="width: {{ $skill['level'] ?? 50 }}%"></div>
                            </div>
                            <p class="text-xs text-zinc-400 mt-1.5 text-right">{{ $skill['level'] ?? 50 }}%</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-12 text-center">
                    <a href="{{ route('landing.skills') }}" wire:navigate
                        class="inline-flex items-center gap-2 border-2 border-emerald-600 text-emerald-600 dark:border-emerald-400 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white dark:hover:bg-emerald-400 dark:hover:text-zinc-900 font-semibold text-sm px-8 py-3.5 rounded-xl transition">
                        Lihat Semua Skills
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>
    @endif

    <section class="py-24 bg-zinc-50 dark:bg-zinc-900/50">
        <div class="max-w-4xl mx-auto px-6">
            <div
                class="relative bg-gradient-to-br from-blue-600 to-purple-700 rounded-3xl p-12 md:p-16 text-center overflow-hidden">
                {{-- Pattern --}}
                <div class="absolute inset-0 opacity-[0.07]"
                    style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 24px 24px;">
                </div>
                {{-- Blobs --}}
                <div class="absolute -top-16 -right-16 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-16 -left-16 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>

                <div class="relative">
                    <p class="text-blue-200 text-sm font-semibold uppercase tracking-wider mb-4">Let's Work Together
                    </p>
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                        Have a project in mind?
                    </h2>
                    <p class="text-blue-100 text-lg mb-8 max-w-lg mx-auto">
                        {{ $hero['cta_section_text'] ?? "I'd love to hear about it. Let's build something great together." }}
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center gap-3">
                        <a href="{{ route('landing.contact') }}" wire:navigate
                            class="inline-flex items-center justify-center gap-2 bg-white text-blue-700 hover:bg-blue-50 font-semibold text-sm px-8 py-3.5 rounded-xl transition shadow-lg">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Get In Touch
                        </a>
                        <a href="{{ route('landing.projects') }}" wire:navigate
                            class="inline-flex items-center justify-center gap-2 bg-white/15 hover:bg-white/25 text-white font-semibold text-sm px-8 py-3.5 rounded-xl border border-white/20 transition">
                            View Projects
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
