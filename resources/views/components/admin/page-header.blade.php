@props(['title', 'subtitle' => null, 'back' => null])

<div class="page-header mb-8 flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        @if ($back)
            <flux:button :href="$back" variant="ghost" icon="arrow-left" size="sm" aria-label="Back" wire:navigate />
        @endif
        <div>
            <flux:heading size="xl" level="1">{{ $title }}</flux:heading>
            @if ($subtitle)
                <flux:subheading>{{ $subtitle }}</flux:subheading>
            @endif
        </div>
    </div>
    {{ $slot }}
</div>
