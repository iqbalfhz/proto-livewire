<?php

use App\Models\Skill;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Skills')] class extends Component {
    public string $name = '';
    public string $icon = '';
    public string $category = '';
    public int $level = 50;
    public int $sort_order = 0;

    public ?int $editingId = null;
    public string $editName = '';
    public string $editIcon = '';
    public string $editCategory = '';
    public int $editLevel = 50;
    public int $editSortOrder = 0;

    public function addSkill(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:500'],
            'category' => ['required', 'string', 'max:100'],
            'level' => ['required', 'integer', 'min:0', 'max:100'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        Skill::create([
            'name' => $this->name,
            'icon' => $this->icon ?: null,
            'category' => $this->category,
            'level' => $this->level,
            'sort_order' => $this->sort_order,
        ]);

        $this->reset(['name', 'icon', 'category', 'level', 'sort_order']);
        $this->level = 50;
        Flux::toast(variant: 'success', text: 'Skill added.');
    }

    public function startEdit(int $id): void
    {
        $skill = Skill::findOrFail($id);
        $this->editingId = $id;
        $this->editName = $skill->name;
        $this->editIcon = $skill->icon ?? '';
        $this->editCategory = $skill->category;
        $this->editLevel = $skill->level;
        $this->editSortOrder = $skill->sort_order ?? 0;
    }

    public function updateSkill(): void
    {
        $this->validate([
            'editName' => ['required', 'string', 'max:100'],
            'editIcon' => ['nullable', 'string', 'max:500'],
            'editCategory' => ['required', 'string', 'max:100'],
            'editLevel' => ['required', 'integer', 'min:0', 'max:100'],
            'editSortOrder' => ['integer', 'min:0'],
        ]);

        Skill::findOrFail($this->editingId)->update([
            'name' => $this->editName,
            'icon' => $this->editIcon ?: null,
            'category' => $this->editCategory,
            'level' => $this->editLevel,
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
        return view('pages.admin.skills-index', ['skills' => $skills])->layout('layouts.admin');
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Skills</flux:heading>
            <flux:subheading>Manage your skills and proficiency levels.</flux:subheading>
        </div>
    </div>

    {{-- Add Skill Form --}}
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 mb-6">
        <flux:heading class="mb-4">Add New Skill</flux:heading>
        <form wire:submit="addSkill">
            <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <flux:input wire:model="name" label="Name" placeholder="Laravel" required />
                <flux:input wire:model="category" label="Category" placeholder="Backend" required />
                <flux:input wire:model="icon" label="Icon URL" placeholder="https://..." />
                <flux:input wire:model="sort_order" type="number" label="Sort Order" min="0" />
            </div>
            <div class="flex items-end gap-4">
                <div class="flex-1">
                    <flux:label>Proficiency Level: {{ $level }}%</flux:label>
                    <input type="range" wire:model.live="level" min="0" max="100"
                        class="w-full mt-1 accent-blue-500" />
                </div>
                <flux:button type="submit" variant="primary" icon="plus">Add Skill</flux:button>
            </div>
        </form>
    </div>

    {{-- Skills List --}}
    @forelse($skills as $category => $categorySkills)
        <div
            class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden mb-4">
            <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50">
                <flux:heading>{{ $category }}</flux:heading>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($categorySkills as $skill)
                        <tr>
                            <td class="px-4 py-3">
                                @if ($editingId === $skill->id)
                                    {{-- Inline Edit Form --}}
                                    <form wire:submit="updateSkill" class="grid sm:grid-cols-4 gap-3 items-end">
                                        <flux:input wire:model="editName" label="Name" required />
                                        <flux:input wire:model="editCategory" label="Category" required />
                                        <flux:input wire:model="editIcon" label="Icon URL" />
                                        <flux:input wire:model="editSortOrder" type="number" label="Order"
                                            min="0" />
                                        <div class="sm:col-span-3">
                                            <flux:label>Level: {{ $editLevel }}%</flux:label>
                                            <input type="range" wire:model.live="editLevel" min="0"
                                                max="100" class="w-full mt-1 accent-blue-500" />
                                        </div>
                                        <div class="flex gap-2">
                                            <flux:button type="submit" variant="primary" size="sm">Save
                                            </flux:button>
                                            <flux:button wire:click="cancelEdit" variant="ghost" size="sm">Cancel
                                            </flux:button>
                                        </div>
                                    </form>
                                @else
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            @if ($skill->icon)
                                                <img src="{{ $skill->icon }}" class="w-6 h-6 object-contain"
                                                    alt="{{ $skill->name }}" onerror="this.style.display='none'">
                                            @endif
                                            <div>
                                                <p class="font-medium">{{ $skill->name }}</p>
                                                <div class="w-40 h-1.5 bg-zinc-100 dark:bg-zinc-700 rounded-full mt-1">
                                                    <div class="h-full bg-blue-500 rounded-full"
                                                        style="width: {{ $skill->level }}%"></div>
                                                </div>
                                            </div>
                                            <span class="text-xs text-zinc-400">{{ $skill->level }}%</span>
                                        </div>
                                        <div class="flex gap-1">
                                            <flux:button wire:click="startEdit({{ $skill->id }})" variant="ghost"
                                                size="sm" icon="pencil" />
                                            <flux:modal.trigger :name="'delete-skill-'.$skill->id">
                                                <flux:button variant="ghost" size="sm" icon="trash"
                                                    class="text-red-500 hover:text-red-600" />
                                            </flux:modal.trigger>
                                        </div>
                                    </div>

                                    <flux:modal :name="'delete-skill-'.$skill->id" class="max-w-sm">
                                        <div class="space-y-4">
                                            <flux:heading>Delete Skill?</flux:heading>
                                            <flux:subheading>This will permanently delete "{{ $skill->name }}".
                                            </flux:subheading>
                                            <div class="flex justify-end gap-2">
                                                <flux:modal.close>
                                                    <flux:button variant="filled">Cancel</flux:button>
                                                </flux:modal.close>
                                                <flux:button wire:click="delete({{ $skill->id }})" variant="danger">
                                                    Delete</flux:button>
                                            </div>
                                        </div>
                                    </flux:modal>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @empty
        <div
            class="py-16 text-center text-zinc-400 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl">
            <flux:icon name="wrench-screwdriver" class="size-10 mx-auto mb-3 opacity-40" />
            <p>No skills yet. Add your first skill above.</p>
        </div>
    @endforelse
</div>
