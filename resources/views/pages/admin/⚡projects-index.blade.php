<?php

use App\Models\Project;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Projects')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleFeatured(int $id): void
    {
        $project = Project::findOrFail($id);
        $project->update(['is_featured' => !$project->is_featured]);
        Flux::toast(variant: 'success', text: $project->fresh()->is_featured ? 'Marked as featured.' : 'Removed from featured.');
    }

    public function delete(int $id): void
    {
        Project::findOrFail($id)->delete();
        Flux::toast(variant: 'success', text: 'Project deleted.');
    }

    public function render()
    {
        $projects = Project::when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))->ordered()->paginate(15);

        return view('pages.admin.projects-index', ['projects' => $projects])->layout('layouts.admin');
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Projects</flux:heading>
            <flux:subheading>Manage your portfolio projects.</flux:subheading>
        </div>
        <flux:button :href="route('admin.projects.create')" variant="primary" icon="plus" wire:navigate>New Project
        </flux:button>
    </div>

    <div class="mb-5 max-w-xs">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search projects..." icon="magnifying-glass" />
    </div>

    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
        @if ($projects->isEmpty())
            <div class="py-16 text-center text-zinc-400">
                <flux:icon name="folder" class="size-10 mx-auto mb-3 opacity-40" />
                <p>No projects yet. <a href="{{ route('admin.projects.create') }}" wire:navigate
                        class="text-blue-500 hover:underline">Add one!</a></p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="border-b border-zinc-200 dark:border-zinc-700 text-xs text-zinc-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Title</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Tech</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Featured</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($projects as $project)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                            <td class="px-4 py-3 font-medium max-w-xs truncate">{{ $project->title }}</td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <div class="flex flex-wrap gap-1">
                                    @foreach (array_slice((array) $project->tech_stack, 0, 3) as $tech)
                                        <flux:badge size="sm" color="zinc">{{ $tech }}</flux:badge>
                                    @endforeach
                                    @if (count((array) $project->tech_stack) > 3)
                                        <flux:badge size="sm" color="zinc">
                                            +{{ count((array) $project->tech_stack) - 3 }}</flux:badge>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                @if ($project->is_featured)
                                    <flux:badge color="amber" size="sm">Featured</flux:badge>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <flux:tooltip
                                        :content="$project->is_featured ? 'Remove featured' : 'Mark featured'">
                                        <flux:button wire:click="toggleFeatured({{ $project->id }})" variant="ghost"
                                            size="sm" :icon="$project->is_featured ? 'star' : 'star'" />
                                    </flux:tooltip>
                                    <flux:button :href="route('admin.projects.edit', $project)" variant="ghost"
                                        size="sm" icon="pencil" wire:navigate />
                                    <flux:modal.trigger :name="'delete-project-'.$project->id">
                                        <flux:button variant="ghost" size="sm" icon="trash"
                                            class="text-red-500 hover:text-red-600" />
                                    </flux:modal.trigger>
                                </div>

                                <flux:modal :name="'delete-project-'.$project->id" class="max-w-sm">
                                    <div class="space-y-4">
                                        <flux:heading>Delete Project?</flux:heading>
                                        <flux:subheading>This will permanently delete "{{ $project->title }}".
                                        </flux:subheading>
                                        <div class="flex justify-end gap-2">
                                            <flux:modal.close>
                                                <flux:button variant="filled">Cancel</flux:button>
                                            </flux:modal.close>
                                            <flux:button wire:click="delete({{ $project->id }})" variant="danger">
                                                Delete</flux:button>
                                        </div>
                                    </div>
                                </flux:modal>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="px-4 py-3 border-t border-zinc-200 dark:border-zinc-700">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
</div>
