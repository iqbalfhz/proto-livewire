@props([
    'sidebar' => false,
])

@php
    $__about = \App\Models\SiteContent::group('about');
    $__appName = $__about['name'] ?? config('app.name', 'Portfolio');
@endphp

@if ($sidebar)
    <flux:sidebar.brand :name="$__appName" {{ $attributes }}>
        <x-slot name="logo"
            class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="$__appName" {{ $attributes }}>
        <x-slot name="logo"
            class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
            <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
        </x-slot>
    </flux:brand>
@endif
