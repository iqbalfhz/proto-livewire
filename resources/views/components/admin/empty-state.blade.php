@props(['icon'])

<div {{ $attributes->class('py-16 text-center text-ink-muted') }}>
    <flux:icon :name="$icon" class="mx-auto mb-3 size-10 opacity-40" />
    <p>{{ $slot }}</p>
</div>
