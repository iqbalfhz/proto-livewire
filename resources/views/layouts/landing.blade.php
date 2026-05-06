<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-900 text-zinc-800 dark:text-zinc-100">

    <!-- Navbar -->
    <flux:header sticky
        class="border-b border-zinc-200 dark:border-zinc-700 bg-white/90 dark:bg-zinc-900/90 backdrop-blur-sm">
        <flux:brand href="{{ route('landing.home') }}" wire:navigate class="font-bold text-lg">
            {{ config('app.name', 'Portfolio') }}
        </flux:brand>

        <flux:spacer />

        <flux:navbar class="hidden md:flex gap-1">
            <flux:navbar.item :href="route('landing.home')" wire:navigate :current="request()->routeIs('landing.home')">
                Home
            </flux:navbar.item>
            <flux:navbar.item :href="route('landing.blog')" wire:navigate
                :current="request()->routeIs('landing.blog*')">
                Blog
            </flux:navbar.item>
            <flux:navbar.item :href="route('landing.projects')" wire:navigate
                :current="request()->routeIs('landing.projects')">
                Project
            </flux:navbar.item>
            <flux:navbar.item :href="route('landing.about')" wire:navigate
                :current="request()->routeIs('landing.about')">
                About
            </flux:navbar.item>
            <flux:navbar.item :href="route('landing.skills')" wire:navigate
                :current="request()->routeIs('landing.skills')">
                Skill
            </flux:navbar.item>
            <flux:navbar.item :href="route('landing.contact')" wire:navigate
                :current="request()->routeIs('landing.contact')">
                Contact
            </flux:navbar.item>
        </flux:navbar>

        <div class="flex items-center gap-2 ms-4">
            <flux:tooltip content="Toggle dark mode">
                <flux:button icon="sun" variant="ghost" size="sm" x-data
                    x-on:click="$flux.appearance = $flux.appearance === 'dark' ? 'light' : 'dark'" />
            </flux:tooltip>

            @auth
                <flux:button :href="auth()->user()->isAdmin() ? route('admin.dashboard') : route('dashboard')"
                    size="sm" variant="filled" wire:navigate>
                    Dashboard
                </flux:button>
            @else
                <flux:button :href="route('login')" size="sm" variant="primary" wire:navigate>
                    Login
                </flux:button>
            @endauth
        </div>

        <!-- Mobile menu -->
        <flux:dropdown position="bottom" align="end" class="md:hidden">
            <flux:button icon="bars-3" variant="ghost" />
            <flux:menu>
                <flux:menu.item :href="route('landing.home')" wire:navigate>Home</flux:menu.item>
                <flux:menu.item :href="route('landing.blog')" wire:navigate>Blog</flux:menu.item>
                <flux:menu.item :href="route('landing.projects')" wire:navigate>Project</flux:menu.item>
                <flux:menu.item :href="route('landing.about')" wire:navigate>About</flux:menu.item>
                <flux:menu.item :href="route('landing.skills')" wire:navigate>Skill</flux:menu.item>
                <flux:menu.item :href="route('landing.contact')" wire:navigate>Contact</flux:menu.item>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    <main>
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="border-t border-zinc-200 dark:border-zinc-700 py-8 mt-16">
        <div
            class="max-w-5xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-zinc-500">
            <span>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</span>
            <div class="flex gap-4">
                <a href="{{ route('landing.home') }}" wire:navigate
                    class="hover:text-zinc-800 dark:hover:text-zinc-100 transition">Home</a>
                <a href="{{ route('landing.blog') }}" wire:navigate
                    class="hover:text-zinc-800 dark:hover:text-zinc-100 transition">Blog</a>
                <a href="{{ route('landing.contact') }}" wire:navigate
                    class="hover:text-zinc-800 dark:hover:text-zinc-100 transition">Contact</a>
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
