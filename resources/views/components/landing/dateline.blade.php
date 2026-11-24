@props(['left', 'right' => null])

<div class="flex items-center gap-4 border-b border-rule py-4">
    <span class="label">{{ $left }}</span>
    <span class="h-px flex-1 bg-rule"></span>
    @if ($right)
        <span class="label">{{ $right }}</span>
    @endif
</div>
