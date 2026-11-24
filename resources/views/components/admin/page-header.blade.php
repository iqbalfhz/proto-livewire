@props(['back', 'title', 'subtitle'])

<div class="flex items-center gap-3 mb-8">
    <flux:button :href="$back" variant="ghost" icon="arrow-left" size="sm" wire:navigate />
    <div>
        <flux:heading size="xl">{{ $title }}</flux:heading>
        <flux:subheading>{{ $subtitle }}</flux:subheading>
    </div>
</div>
