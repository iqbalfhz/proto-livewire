@props(['name', 'heading', 'action'])

<flux:modal :name="$name" class="max-w-sm">
    <div class="space-y-4">
        <flux:heading>{{ $heading }}</flux:heading>
        <flux:subheading>{{ $slot }}</flux:subheading>
        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="filled">Cancel</flux:button>
            </flux:modal.close>
            <flux:button wire:click="{{ $action }}" variant="danger">Delete</flux:button>
        </div>
    </div>
</flux:modal>
