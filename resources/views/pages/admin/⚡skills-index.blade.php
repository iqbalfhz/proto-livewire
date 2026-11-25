<?php

use App\Models\Skill;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Skills')] class extends Component {
    public string $name = '';
    public string $icon = '';
    public string $category = '';
    public int $sort_order = 0;

    public ?int $editingId = null;
    public string $editName = '';
    public string $editIcon = '';
    public string $editCategory = '';
    public int $editSortOrder = 0;

    public function addSkill(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:500'],
            'category' => ['required', 'string', 'max:100'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        Skill::create([
            'name' => $this->name,
            'icon' => $this->icon ?: null,
            'category' => $this->category,
            'sort_order' => $this->sort_order,
        ]);

        $this->reset(['name', 'icon', 'category', 'sort_order']);
        Flux::toast(variant: 'success', text: 'Skill added.');
    }

    public function startEdit(int $id): void
    {
        $skill = Skill::findOrFail($id);
        $this->editingId = $id;
        $this->editName = $skill->name;
        $this->editIcon = $skill->icon ?? '';
        $this->editCategory = $skill->category;
        $this->editSortOrder = $skill->sort_order ?? 0;
    }

    public function updateSkill(): void
    {
        $this->validate([
            'editName' => ['required', 'string', 'max:100'],
            'editIcon' => ['nullable', 'string', 'max:500'],
            'editCategory' => ['required', 'string', 'max:100'],
            'editSortOrder' => ['integer', 'min:0'],
        ]);

        Skill::findOrFail($this->editingId)->update([
            'name' => $this->editName,
            'icon' => $this->editIcon ?: null,
            'category' => $this->editCategory,
            'sort_order' => $this->editSortOrder,
        ]);

        $this->editingId = null;
        Flux::toast(variant: 'success', text: 'Skill updated.');
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
    }

    public function delete(int $id): void
    {
        Skill::findOrFail($id)->delete();
        Flux::toast(variant: 'success', text: 'Skill deleted.');
    }

    public function render()
    {
        $skills = Skill::ordered()->get()->groupBy('category');
        return $this->view(['skills' => $skills])->layout('layouts.admin');
    }
}; ?>

<div>
    <x-admin.page-header title="Skills" subtitle="Grouped by category on the public page" />

    <form wire:submit="addSkill" class="admin-panel mb-6 p-6">
        <h2 class="label mb-4">Add a skill</h2>
        <div class="grid items-end gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <flux:input wire:model="name" label="Name" placeholder="Laravel" required />
            <flux:input wire:model="category" label="Category" placeholder="Backend" required />
            <flux:input wire:model="icon" label="Icon URL" placeholder="https://..." />
            <flux:input wire:model="sort_order" type="number" label="Sort Order" min="0" />
            <flux:button type="submit" variant="primary" icon="plus">Add Skill</flux:button>
        </div>
    </form>

    @forelse($skills as $category => $categorySkills)
        <div class="admin-panel mb-4 overflow-hidden">
            <h2 class="label border-b border-rule bg-paper-sunk px-4 py-3">{{ $category }}</h2>
            <table class="admin-table">
                <tbody>
                    @foreach ($categorySkills as $skill)
                        <tr>
                            <td>
                                @if ($editingId === $skill->id)
                                    <form wire:submit="updateSkill" class="grid items-end gap-3 sm:grid-cols-2 lg:grid-cols-5">
                                        <flux:input wire:model="editName" label="Name" required />
                                        <flux:input wire:model="editCategory" label="Category" required />
                                        <flux:input wire:model="editIcon" label="Icon URL" />
                                        <flux:input wire:model="editSortOrder" type="number" label="Order" min="0" />
                                        <div class="flex gap-2">
                                            <flux:button type="submit" variant="primary" size="sm">Save</flux:button>
                                            <flux:button wire:click="cancelEdit" variant="ghost" size="sm">Cancel</flux:button>
                                        </div>
                                    </form>
                                @else
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-3">
                                            @if ($skill->icon)
                                                <img src="{{ $skill->icon }}" class="size-6 object-contain"
                                                    alt="{{ $skill->name }}" x-data x-init="if ($el.complete && $el.naturalWidth === 0) $el.style.display = 'none'"
                                                    x-on:error="$el.style.display = 'none'">
                                            @endif
                                            <span class="font-medium">{{ $skill->name }}</span>
                                        </div>
                                        <div class="flex gap-1">
                                            <flux:button wire:click="startEdit({{ $skill->id }})" variant="ghost"
                                                size="sm" icon="pencil" aria-label="Edit skill" />
                                            <x-admin.delete-trigger :name="'delete-skill-'.$skill->id" label="Delete skill" />
                                        </div>
                                    </div>

                                    <x-admin.confirm-delete :name="'delete-skill-'.$skill->id" heading="Delete Skill?" :action="'delete('.$skill->id.')'">
                                        This will permanently delete "{{ $skill->name }}".
                                    </x-admin.confirm-delete>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <x-admin.empty-state icon="wrench-screwdriver" class="admin-panel">No skills yet. Add your first skill above.</x-admin.empty-state>
    @endforelse
</div>
