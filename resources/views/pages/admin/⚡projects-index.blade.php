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

        return $this->view(['projects' => $projects])->layout('layouts.admin');
    }
}; ?>

<div>
    <x-admin.page-header title="Projects" subtitle="Manage your portfolio projects">
        <flux:button :href="route('admin.projects.create')" variant="primary" icon="plus" wire:navigate>New Project
        </flux:button>
    </x-admin.page-header>

    <div class="mb-5 max-w-xs">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search projects..." icon="magnifying-glass" />
    </div>

    <div class="admin-panel overflow-hidden">
        @if ($projects->isEmpty())
            <x-admin.empty-state icon="folder">No projects yet. <a href="{{ route('admin.projects.create') }}" wire:navigate class="text-oxblood underline-offset-4 hover:underline">Add one!</a></x-admin.empty-state>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th class="hidden md:table-cell">Tech</th>
                        <th class="hidden sm:table-cell">Featured</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        <tr>
                            <td class="font-medium max-w-xs truncate">{{ $project->title }}</td>
                            <td class="hidden md:table-cell">
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
                            <td class="hidden sm:table-cell">
                                @if ($project->is_featured)
                                    <flux:badge color="amber" size="sm">Featured</flux:badge>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    <flux:tooltip
                                        :content="$project->is_featured ? 'Remove featured' : 'Mark featured'">
                                        <flux:button wire:click="toggleFeatured({{ $project->id }})" variant="ghost"
                                            size="sm" icon="star"
                                            :aria-label="$project->is_featured ? 'Remove featured' : 'Mark featured'" />
                                    </flux:tooltip>
                                    <flux:button :href="route('admin.projects.edit', $project)" variant="ghost"
                                        size="sm" icon="pencil" aria-label="Edit project" wire:navigate />
                                    <x-admin.delete-trigger :name="'delete-project-'.$project->id" label="Delete project" />
                                </div>

                                <x-admin.confirm-delete :name="'delete-project-'.$project->id" heading="Delete Project?" :action="'delete('.$project->id.')'">
                                    This will permanently delete "{{ $project->title }}".
                                </x-admin.confirm-delete>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($projects->hasPages())
                <div class="admin-panel-footer">{{ $projects->links() }}</div>
            @endif
        @endif
    </div>
</div>
