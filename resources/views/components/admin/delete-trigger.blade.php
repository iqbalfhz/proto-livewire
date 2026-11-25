@props(['name', 'label'])

<flux:modal.trigger :name="$name">
    <flux:button variant="ghost" size="sm" icon="trash" :aria-label="$label"
        class="text-red-600 hover:text-red-700 dark:text-red-400" />
</flux:modal.trigger>
