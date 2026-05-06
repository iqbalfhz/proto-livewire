<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">

    <flux:sidebar sticky collapsible class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            <flux:brand href="{{ route('admin.dashboard') }}" wire:navigate class="font-bold text-sm">
                <flux:icon name="shield-check" class="size-5 text-blue-500" />
                Admin Panel
            </flux:brand>
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav>
            <flux:sidebar.group heading="Content">
                <flux:sidebar.item icon="layout-grid" :href="route('admin.dashboard')"
                    :current="request()->routeIs('admin.dashboard')" wire:navigate>
                    Dashboard
                </flux:sidebar.item>
                <flux:sidebar.item icon="home" :href="route('admin.home')"
                    :current="request()->routeIs('admin.home')" wire:navigate>
                    Home Page
                </flux:sidebar.item>
                <flux:sidebar.item icon="user-circle" :href="route('admin.about')"
                    :current="request()->routeIs('admin.about')" wire:navigate>
                    About
                </flux:sidebar.item>
                <flux:sidebar.item icon="document-text" :href="route('admin.blog.index')"
                    :current="request()->routeIs('admin.blog*')" wire:navigate>
                    Blog
                </flux:sidebar.item>
                <flux:sidebar.item icon="folder" :href="route('admin.projects.index')"
                    :current="request()->routeIs('admin.projects*')" wire:navigate>
                    Projects
                </flux:sidebar.item>
                <flux:sidebar.item icon="wrench-screwdriver" :href="route('admin.skills.index')"
                    :current="request()->routeIs('admin.skills*')" wire:navigate>
                    Skills
                </flux:sidebar.item>
                <flux:sidebar.item icon="envelope" :href="route('admin.messages.index')"
                    :current="request()->routeIs('admin.messages*')" wire:navigate>
                    Messages
                </flux:sidebar.item>
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:spacer />

        <flux:sidebar.nav>
            <flux:sidebar.item icon="arrow-top-right-on-square" :href="route('landing.home')" target="_blank">
                View Site
            </flux:sidebar.item>
        </flux:sidebar.nav>

        <flux:dropdown position="top" align="start" class="hidden lg:block">
            <flux:sidebar.item icon="user-circle" class="cursor-pointer">
                {{ auth()->user()->name }}
            </flux:sidebar.item>
            <flux:menu>
                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>Settings</flux:menu.item>
                <flux:menu.separator />
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle">Logout
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile header -->
    <flux:header class="lg:hidden border-b border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.toggle icon="bars-2" inset="left" />
        <flux:spacer />
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <flux:button type="submit" variant="ghost" size="sm" icon="arrow-right-start-on-rectangle">Logout
            </flux:button>
        </form>
    </flux:header>

    <flux:main class="p-6">
        {{ $slot }}
    </flux:main>

    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>
