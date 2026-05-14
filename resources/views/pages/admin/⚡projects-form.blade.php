<?php

use App\Models\Project;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public ?Project $project = null;

    public string $title = '';
    public string $slug = '';
    public string $description = '';
    public string $tech_stack_input = '';
    public string $demo_url = '';
    public string $repo_url = '';
    public bool $is_featured = false;
    public int $sort_order = 0;
    public $image = null;
    public ?string $existing_image = null;

    public function mount(?Project $project = null): void
    {
        if ($project?->exists) {
            $this->project = $project;
            $this->title = $project->title;
            $this->slug = $project->slug;
            $this->description = $project->description;
            $this->tech_stack_input = implode(', ', (array) $project->tech_stack);
            $this->demo_url = $project->demo_url ?? '';
            $this->repo_url = $project->repo_url ?? '';
            $this->is_featured = $project->is_featured;
            $this->sort_order = $project->sort_order ?? 0;
            $this->existing_image = $project->image;
        }
    }

    public function updatedTitle(): void
    {
        if (!$this->project?->exists) {
            $this->slug = \Illuminate\Support\Str::slug($this->title);
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash'],
            'description' => ['nullable', 'string'],
            'tech_stack_input' => ['nullable', 'string'],
            'demo_url' => ['nullable', 'url', 'max:500'],
            'repo_url' => ['nullable', 'url', 'max:500'],
            'is_featured' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
            'image' => ['nullable', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240'],
        ]);

        $techStack = array_filter(array_map('trim', explode(',', $this->tech_stack_input)));

        $imagePath = $this->existing_image;
        if ($this->image) {
            $imagePath = $this->image->store('projects', 'public');
        }

        $data = [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? '',
            'tech_stack' => array_values($techStack),
            'demo_url' => $validated['demo_url'] ?? null,
            'repo_url' => $validated['repo_url'] ?? null,
            'is_featured' => $validated['is_featured'],
            'sort_order' => $validated['sort_order'],
            'image' => $imagePath,
        ];

        if ($this->project?->exists) {
            $this->project->update($data);
            Flux::toast(variant: 'success', text: 'Project updated.');
            $this->redirectRoute('admin.projects.index', navigate: true);
        } else {
            Project::create($data);
            Flux::toast(variant: 'success', text: 'Project created.');
            $this->redirectRoute('admin.projects.index', navigate: true);
        }
    }

    public function render()
    {
        $title = $this->project?->exists ? 'Edit Project' : 'New Project';
        return $this->view()->title($title)->layout('layouts.admin');
    }
}; ?>

<div>
    <div class="flex items-center gap-3 mb-8">
        <flux:button :href="route('admin.projects.index')" variant="ghost" icon="arrow-left" size="sm"
            wire:navigate />
        <div>
            <flux:heading size="xl">{{ $project?->exists ? 'Edit Project' : 'New Project' }}</flux:heading>
            <flux:subheading>{{ $project?->exists ? 'Update project details.' : 'Add a new portfolio project.' }}
            </flux:subheading>
        </div>
    </div>

    <form wire:submit="save" class="space-y-5">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 space-y-5">
            <flux:input wire:model.live="title" label="Title" placeholder="Project title" required />
            <flux:input wire:model="slug" label="Slug" placeholder="project-slug" required />
            <flux:textarea wire:model="description" label="Description" rows="5"
                placeholder="Describe the project..." />
            <flux:input wire:model="tech_stack_input" label="Tech Stack (comma-separated)"
                placeholder="Laravel, Vue.js, Tailwind CSS" />

            <div class="grid sm:grid-cols-2 gap-5">
                <flux:input wire:model="demo_url" type="url" label="Demo URL" placeholder="https://..." />
                <flux:input wire:model="repo_url" type="url" label="Repository URL"
                    placeholder="https://github.com/..." />
            </div>

            <div class="grid sm:grid-cols-2 gap-5">
                <flux:input wire:model="sort_order" type="number" label="Sort Order" min="0" />
                <div class="flex items-center gap-3 mt-6">
                    <flux:checkbox wire:model="is_featured" id="is_featured" />
                    <flux:label for="is_featured">Featured Project</flux:label>
                </div>
            </div>

            {{-- Image Upload --}}
            <div>
                <flux:label>Project Image</flux:label>
                <div class="mt-2 flex items-center gap-4">
                    @if ($image)
                        <img src="{{ $image->temporaryUrl() }}"
                            class="w-32 h-20 object-cover rounded-lg ring-2 ring-blue-500" alt="Preview">
                    @elseif($existing_image)
                        <img src="{{ Storage::url($existing_image) }}"
                            class="w-32 h-20 object-cover rounded-lg ring-2 ring-zinc-300 dark:ring-zinc-600"
                            alt="Current image">
                    @endif
                    <flux:input type="file" wire:model="image" accept="image/*" />
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <flux:button :href="route('admin.projects.index')" variant="ghost" wire:navigate>Cancel</flux:button>
            <flux:button type="submit" variant="primary">
                {{ $project?->exists ? 'Update Project' : 'Create Project' }}
            </flux:button>
        </div>
    </form>
</div>
