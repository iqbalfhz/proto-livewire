<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-950 text-zinc-800 dark:text-zinc-100">

    @php
        $__about = \App\Models\SiteContent::group('about');
        $__fullName = $__about['name'] ?? config('app.name', 'Dev');
        $__firstName = explode(' ', trim($__fullName))[0];
    @endphp

    <!-- Navbar -->
    <flux:header sticky
        class="border-b border-zinc-200/80 dark:border-zinc-800/80 bg-white/80 dark:bg-zinc-950/80 backdrop-blur-md">

        <flux:brand href="{{ route('landing.home') }}" wire:navigate class="font-bold text-base tracking-tight">
            {{ $__firstName }}
        </flux:brand>

        <flux:spacer />

        <flux:navbar class="hidden md:flex gap-0.5">
            <flux:navbar.item :href="route('landing.home')" wire:navigate :current="request()->routeIs('landing.home')"
                class="text-sm">Home</flux:navbar.item>
            <flux:navbar.item :href="route('landing.blog')" wire:navigate :current="request()->routeIs('landing.blog*')"
                class="text-sm">Blog</flux:navbar.item>
            <flux:navbar.item :href="route('landing.projects')" wire:navigate
                :current="request()->routeIs('landing.projects')" class="text-sm">Projects</flux:navbar.item>
            <flux:navbar.item :href="route('landing.about')" wire:navigate
                :current="request()->routeIs('landing.about')" class="text-sm">About</flux:navbar.item>
            <flux:navbar.item :href="route('landing.skills')" wire:navigate
                :current="request()->routeIs('landing.skills')" class="text-sm">Skills</flux:navbar.item>
            <flux:navbar.item :href="route('landing.contact')" wire:navigate
                :current="request()->routeIs('landing.contact')" class="text-sm">Contact</flux:navbar.item>
        </flux:navbar>

        <div class="flex items-center gap-2 ms-3">
            <flux:button icon="moon" variant="ghost" size="sm" x-data
                x-on:click="$flux.appearance = $flux.appearance === 'dark' ? 'light' : 'dark'" class="text-zinc-500" />

            @auth
                <flux:button :href="auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard')"
                    size="sm" variant="primary" wire:navigate>
                    Dashboard
                </flux:button>
            @endauth
        </div>

        <!-- Mobile menu -->
        <flux:dropdown position="bottom" align="end" class="md:hidden ms-1">
            <flux:button icon="bars-3" variant="ghost" size="sm" />
            <flux:menu>
                <flux:menu.item :href="route('landing.home')" wire:navigate>Home</flux:menu.item>
                <flux:menu.item :href="route('landing.blog')" wire:navigate>Blog</flux:menu.item>
                <flux:menu.item :href="route('landing.projects')" wire:navigate>Projects</flux:menu.item>
                <flux:menu.item :href="route('landing.about')" wire:navigate>About</flux:menu.item>
                <flux:menu.item :href="route('landing.skills')" wire:navigate>Skills</flux:menu.item>
                <flux:menu.item :href="route('landing.contact')" wire:navigate>Contact</flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="border-t border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900/50 mt-0">
        <div class="max-w-6xl mx-auto px-6 py-12">
            <div class="flex flex-col md:flex-row items-start justify-between gap-8 mb-8">
                <div class="max-w-xs">
                    <a href="{{ route('landing.home') }}" wire:navigate
                        class="font-bold text-lg tracking-tight inline-block mb-2">
                        {{ $__firstName }}
                    </a>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">Building modern web experiences with passion and
                        precision.</p>
                </div>
                <div class="grid grid-cols-2 gap-x-16 gap-y-2 text-sm">
                    <a href="{{ route('landing.home') }}" wire:navigate
                        class="text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition">Home</a>
                    <a href="{{ route('landing.skills') }}" wire:navigate
                        class="text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition">Skills</a>
                    <a href="{{ route('landing.blog') }}" wire:navigate
                        class="text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition">Blog</a>
                    <a href="{{ route('landing.contact') }}" wire:navigate
                        class="text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition">Contact</a>
                    <a href="{{ route('landing.projects') }}" wire:navigate
                        class="text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition">Projects</a>
                    <a href="{{ route('landing.about') }}" wire:navigate
                        class="text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 transition">About</a>
                </div>
            </div>
            <div
                class="border-t border-zinc-200 dark:border-zinc-800 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-zinc-400">
                <span>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</span>
                <span>Built with Laravel &amp; Livewire</span>
            </div>
        </div>
    </footer>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>
