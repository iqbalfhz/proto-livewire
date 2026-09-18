@php
    $__about = \App\Models\SiteContent::group('about');
    $__contact = \App\Models\SiteContent::group('contact');
    $__fullName = $__about['name'] ?? config('app.name', 'Dev');
    $__firstName = explode(' ', trim($__fullName))[0];
    $__role = $__about['role'] ?? 'Developer';
    $__defaultImage = ($__about['photo'] ?? null) ? Storage::disk('public')->url($__about['photo']) : null;

    $__nav = [
        ['route' => 'landing.projects', 'label' => 'Projects', 'pattern' => 'landing.projects*'],
        ['route' => 'landing.blog', 'label' => 'Blog', 'pattern' => 'landing.blog*'],
        ['route' => 'landing.skills', 'label' => 'Skills', 'pattern' => 'landing.skills'],
        ['route' => 'landing.about', 'label' => 'About', 'pattern' => 'landing.about'],
        ['route' => 'landing.contact', 'label' => 'Contact', 'pattern' => 'landing.contact'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head', [
        'description' => $description ?? ($__about['bio'] ?? null),
        'ogImage' => $ogImage ?? $__defaultImage,
        'ogType' => $ogType ?? 'website',
    ])
</head>

<body class="grain min-h-screen bg-paper text-ink antialiased selection:bg-oxblood">

    {{-- ══════════════════ MASTHEAD ══════════════════ --}}
    <header class="sticky top-0 z-50 border-b border-rule bg-paper/85 backdrop-blur-md">
        {{-- Top rule: name, role, live clock — the newspaper dateline --}}
        <div class="hidden border-b border-rule md:block">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-2">
                <span class="label">{{ $__role }}</span>
                <span class="label">
                    {{ $__contact['location'] ?? 'Indonesia' }} &nbsp;·&nbsp;
                    <span data-clock class="tabular-nums">--:--</span>
                </span>
            </div>
        </div>

        <nav class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-6 py-4">
            <a href="{{ route('landing.home') }}" wire:navigate
                class="display display-sm leading-none tracking-tight hover:text-oxblood transition-colors duration-300">
                {{ $__firstName }}<span class="text-oxblood">.</span>
            </a>

            {{-- Desktop nav --}}
            <div class="hidden items-center gap-8 md:flex">
                @foreach ($__nav as $item)
                    <a href="{{ route($item['route']) }}" wire:navigate
                        class="label link-sweep {{ request()->routeIs($item['pattern']) ? 'link-retract text-ink!' : '' }} hover:text-ink! transition-colors duration-300">
                        {{ $item['label'] }}
                    </a>
                @endforeach

                @auth
                    <a href="{{ route('admin.dashboard') }}" wire:navigate class="label link-sweep hover:text-ink!">
                        Desk
                    </a>
                @endauth

                <button type="button" x-data
                    x-on:click="$flux.appearance = $flux.appearance === 'dark' ? 'light' : 'dark'"
                    class="border border-rule p-2 text-ink-muted transition-colors duration-300 hover:border-rule-strong hover:text-ink"
                    aria-label="Toggle light and dark">
                    <flux:icon name="moon" class="size-4 dark:hidden" />
                    <flux:icon name="sun" class="hidden size-4 dark:block" />
                </button>
            </div>

            {{-- Mobile --}}
            {{-- The overlay is teleported to <body>, so it is not governed by this
                 element's md:hidden — close it if the viewport grows past the
                 breakpoint, e.g. a tablet turned to landscape. --}}
            <div class="flex items-center gap-2 md:hidden" x-data="{ open: false }"
                x-effect="document.body.style.overflow = open ? 'hidden' : ''"
                x-on:resize.window="if (window.innerWidth >= 768) open = false">
                <button type="button" x-data
                    x-on:click="$flux.appearance = $flux.appearance === 'dark' ? 'light' : 'dark'"
                    class="border border-rule p-2 text-ink-muted" aria-label="Toggle light and dark">
                    <flux:icon name="moon" class="size-4 dark:hidden" />
                    <flux:icon name="sun" class="hidden size-4 dark:block" />
                </button>

                <button type="button" x-on:click="open = true" class="border border-rule p-2 text-ink"
                    aria-controls="mobile-menu" x-bind:aria-expanded="open ? 'true' : 'false'"
                    aria-label="Open menu">
                    <flux:icon name="bars-2" class="size-4" />
                </button>

                {{-- Teleported to <body> on purpose. The header sets backdrop-filter,
                     which makes it the containing block for fixed-position children —
                     left inside, this overlay would be trapped in the header strip
                     instead of covering the screen. --}}
                <template x-teleport="body">
                    <div id="mobile-menu" x-cloak x-show="open" x-transition.opacity
                        class="fixed inset-0 z-9999 flex flex-col overflow-y-auto overscroll-contain bg-paper px-6 py-5"
                        x-on:keydown.escape.window="open = false" role="dialog" aria-modal="true"
                        aria-label="Site menu">
                        <div class="flex items-center justify-between">
                            <span class="display display-sm">{{ $__firstName }}<span
                                    class="text-oxblood">.</span></span>
                            <button type="button" x-on:click="open = false" class="border border-rule p-2"
                                aria-label="Close menu">
                                <flux:icon name="x-mark" class="size-4" />
                            </button>
                        </div>

                        <div class="mt-10 flex flex-col">
                            @foreach ($__nav as $i => $item)
                                <a href="{{ route($item['route']) }}" wire:navigate x-on:click="open = false"
                                    class="flex items-baseline gap-4 border-b border-rule py-5">
                                    <span class="index-number">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="display display-md">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>

                        <div class="mt-auto flex items-center justify-between pt-8">
                            <span class="label">{{ $__contact['location'] ?? 'Indonesia' }}</span>
                            <span class="label"><span data-clock class="tabular-nums">--:--</span></span>
                        </div>
                    </div>
                </template>
            </div>
        </nav>
    </header>

    <main>
        {{ $slot }}
    </main>

    {{-- ══════════════════ COLOPHON ══════════════════ --}}
    <footer class="mt-24 border-t border-rule-strong bg-paper-sunk">
        {{-- Ticker --}}
        <div class="marquee border-b border-rule py-3">
            <div class="marquee__track">
                @foreach (['Available for work', '—', 'Laravel', '·', 'Livewire', '·', 'Tailwind CSS', '—', 'Built with care', '·', str(config('app.name'))->upper()] as $word)
                    <span class="label whitespace-nowrap">{{ $word }}</span>
                @endforeach
            </div>
        </div>

        <div class="mx-auto max-w-6xl px-6 py-16">
            <div class="grid gap-12 md:grid-cols-12">
                <div class="md:col-span-5">
                    <p class="label mb-4">Let's build something</p>
                    <a href="{{ route('landing.contact') }}" wire:navigate
                        class="display display-lg block leading-[0.9] hover:text-oxblood transition-colors duration-500">
                        Start a<br><em>conversation</em>
                    </a>
                </div>

                <div class="md:col-span-3 md:col-start-8">
                    <p class="label mb-5">Pages</p>
                    <ul class="space-y-2.5">
                        @foreach ($__nav as $item)
                            <li>
                                <a href="{{ route($item['route']) }}" wire:navigate
                                    class="link-sweep text-sm text-ink-soft hover:text-ink transition-colors">
                                    {{ $item['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="md:col-span-2">
                    <p class="label mb-5">Elsewhere</p>
                    <ul class="space-y-2.5">
                        @foreach (['github_url' => 'GitHub', 'linkedin_url' => 'LinkedIn', 'twitter_url' => 'Twitter'] as $key => $label)
                            @if (!empty($__about[$key]))
                                <li>
                                    <a href="{{ $__about[$key] }}" target="_blank" rel="noopener"
                                        class="link-sweep text-sm text-ink-soft hover:text-ink transition-colors">
                                        {{ $label }} ↗
                                    </a>
                                </li>
                            @endif
                        @endforeach
                        <li>
                            <a href="{{ route('feed.rss') }}"
                                class="link-sweep text-sm text-ink-soft hover:text-ink transition-colors">
                                RSS ↗
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div
                class="mt-16 flex flex-col gap-3 border-t border-rule pt-6 sm:flex-row sm:items-center sm:justify-between">
                <span class="label">© {{ date('Y') }} {{ $__fullName }}</span>
                <span class="label">Set in Instrument Serif &amp; Sans</span>
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
