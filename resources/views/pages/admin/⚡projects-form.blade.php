<?php

use App\Models\Project;
use App\Support\Text;
use App\Support\UploadedImages;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public ?Project $project = null;

    public string $title = '';
    public string $slug = '';
    public string $description = '';
    public string $role = '';
    public string $year = '';
    public string $problem = '';
    public string $approach = '';
    public string $outcome = '';
    public string $tech_stack_input = '';
    public string $demo_url = '';
    public string $repo_url = '';
    public bool $is_featured = false;
    public int $sort_order = 0;
    public $image = null;

    #[Locked]
    public ?string $existing_image = null;

    /** @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile> */
    public array $gallery_uploads = [];

    /** @var array<int, string> Captions keyed by ProjectImage id. */
    public array $gallery_captions = [];

    public function mount(?Project $project = null): void
    {
        if ($project?->exists) {
            $this->project = $project;
            $this->gallery_captions = $project->images->pluck('caption', 'id')
                ->map(fn (?string $caption) => (string) $caption)
                ->all();
            $this->title = $project->title;
            $this->slug = $project->slug;
            $this->description = $project->description;
            $this->role = $project->role ?? '';
            $this->year = $project->year ?? '';
            $this->problem = $project->problem ?? '';
            $this->approach = $project->approach ?? '';
            $this->outcome = $project->outcome ?? '';
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

    /** Store picked screenshots immediately, so long uploads survive an abandoned form. */
    public function updatedGalleryUploads(): void
    {
        if (!$this->project?->exists) {
            return;
        }

        try {
            $this->validate([
                'gallery_uploads.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240'],
            ]);
        } catch (ValidationException $exception) {
            // Livewire appends each new pick, so a rejected file would fail every later batch.
            $this->reset('gallery_uploads');

            throw $exception;
        }

        $nextOrder = (int) $this->project->images()->max('sort_order');

        foreach ($this->gallery_uploads as $upload) {
            $image = $this->project->images()->create([
                'path' => $upload->store('projects/gallery', 'public'),
                'sort_order' => ++$nextOrder,
            ]);

            $this->gallery_captions[$image->id] = '';
        }

        $this->reset('gallery_uploads');
        $this->project->load('images');

        Flux::toast(variant: 'success', text: 'Screenshots added.');
    }

    public function saveCaption(int $imageId): void
    {
        $image = $this->project?->images()->find($imageId);

        if (!$image) {
            return;
        }

        $caption = trim((string) ($this->gallery_captions[$imageId] ?? ''));

        $image->update(['caption' => $caption === '' ? null : mb_substr($caption, 0, 160)]);

        Flux::toast(variant: 'success', text: 'Caption saved.');
    }

    public function moveImage(int $imageId, string $direction): void
    {
        $images = $this->project?->images()->get();

        if (!$images) {
            return;
        }

        $images = $images->values();
        $index = $images->search(fn($image) => $image->id === $imageId);
        $target = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index === false || !$images->has($target)) {
            return;
        }

        $ordered = $images->all();
        [$ordered[$index], $ordered[$target]] = [$ordered[$target], $ordered[$index]];

        // Renumber the whole run: rows can share a sort_order, so swapping values may change nothing.
        foreach ($ordered as $position => $image) {
            $image->update(['sort_order' => $position + 1]);
        }

        $this->project->load('images');
    }

    public function deleteImage(int $imageId): void
    {
        $this->project?->images()->find($imageId)?->delete();

        unset($this->gallery_captions[$imageId]);
        $this->project?->load('images');

        Flux::toast(variant: 'success', text: 'Screenshot removed.');
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('projects', 'slug')->ignore($this->project?->id)],
            'description' => ['nullable', 'string'],
            'role' => ['nullable', 'string', 'max:150'],
            'year' => ['nullable', 'string', 'max:20'],
            'problem' => ['nullable', 'string', 'max:5000'],
            'approach' => ['nullable', 'string', 'max:5000'],
            'outcome' => ['nullable', 'string', 'max:5000'],
            'tech_stack_input' => ['nullable', 'string'],
            'demo_url' => ['nullable', 'url', 'max:500'],
            'repo_url' => ['nullable', 'url', 'max:500'],
            'is_featured' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
            'image' => ['nullable', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240'],
        ]);

        $imagePath = $this->existing_image;
        if ($this->image) {
            $imagePath = $this->image->store('projects', 'public');
            UploadedImages::replace($this->existing_image, $imagePath);
            $this->existing_image = $imagePath;
        }

        $data = [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? '',
            'role' => $validated['role'] ?: null,
            'year' => $validated['year'] ?: null,
            'problem' => $validated['problem'] ?: null,
            'approach' => $validated['approach'] ?: null,
            'outcome' => $validated['outcome'] ?: null,
            'tech_stack' => Text::commaList($this->tech_stack_input),
            'demo_url' => $validated['demo_url'] ?? null,
            'repo_url' => $validated['repo_url'] ?? null,
            'is_featured' => $validated['is_featured'],
            'sort_order' => $validated['sort_order'],
            'image' => $imagePath,
        ];

        if ($this->project?->exists) {
            $this->project->update($data);
            Flux::toast(variant: 'success', text: 'Project updated.');
        } else {
            Project::create($data);
            Flux::toast(variant: 'success', text: 'Project created.');
        }

        $this->redirectRoute('admin.projects.index', navigate: true);
    }

    public function render()
    {
        $title = $this->project?->exists ? 'Edit Project' : 'New Project';
        return $this->view()->title($title)->layout('layouts.admin');
    }
}; ?>

<div>
    <x-admin.page-header :back="route('admin.projects.index')" :title="$project?->exists ? 'Edit Project' : 'New Project'"
        :subtitle="$project?->exists ? 'Update project details' : 'Add a new portfolio project'" />

    <form wire:submit="save" class="space-y-5">
        <div class="admin-panel p-6 space-y-5">
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
                <flux:checkbox wire:model="is_featured" label="Featured project" class="mt-6" />
            </div>

            {{-- Image Upload --}}
            <div>
                <flux:label>Project Image</flux:label>
                <div class="mt-2 flex items-center gap-4">
                    @if ($image)
                        <img src="{{ $image->temporaryUrl() }}"
                            class="w-32 h-20 object-cover rounded-lg ring-2 ring-oxblood" alt="Preview">
                    @elseif($existing_image)
                        <img src="{{ Storage::disk('public')->url($existing_image) }}"
                            class="w-32 h-20 object-cover rounded-lg ring-2 ring-zinc-300 dark:ring-zinc-600"
                            alt="Current image">
                    @endif
                    <flux:input type="file" wire:model="image" accept="image/*" aria-label="Project image" />
                </div>
            </div>
        </div>

        {{-- ── Case study ────────────────────────────────────────── --}}
        <div class="admin-panel p-6 space-y-5">
            <div>
                <flux:heading size="lg">Case study</flux:heading>
                <flux:subheading>Optional. Empty fields stay hidden on the public page.</flux:subheading>
            </div>
            <div class="grid sm:grid-cols-2 gap-5">
                <flux:input wire:model="role" label="Your role" placeholder="Lead developer" />
                <flux:input wire:model="year" label="Year" placeholder="2025" />
            </div>
            <flux:textarea wire:model="problem" label="The problem" rows="4"
                placeholder="What was broken, slow, or missing?" />
            <flux:textarea wire:model="approach" label="The approach" rows="4"
                placeholder="What you decided, and why." />
            <flux:textarea wire:model="outcome" label="The outcome" rows="4"
                placeholder="What changed once it shipped." />
        </div>

        {{-- ── Gallery ───────────────────────────────────────────── --}}
        <div class="admin-panel p-6">
            <div class="mb-1 flex items-baseline justify-between gap-4">
                <flux:heading size="lg">Screenshots</flux:heading>
                @if ($project?->exists)
                    <span class="text-xs text-ink-muted">{{ $project->images->count() }} added</span>
                @endif
            </div>
            <flux:subheading class="mb-5">
                Extra images shown below the main one — one per module works well for larger systems.
            </flux:subheading>

            @if (!$project?->exists)
                <flux:callout variant="secondary" icon="information-circle" class="text-sm">
                    Save the project first, then you can add screenshots here.
                </flux:callout>
            @else
                <div class="mb-5">
                    <flux:input type="file" wire:model="gallery_uploads" accept="image/*" multiple aria-label="Add screenshots" />
                    <div wire:loading wire:target="gallery_uploads" class="mt-2 text-xs text-ink-muted">
                        Uploading…
                    </div>
                    @error('gallery_uploads.*')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                @if ($project->images->isEmpty())
                    <p class="py-6 text-center text-sm text-ink-muted">No screenshots yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($project->images as $galleryImage)
                            <div wire:key="gallery-{{ $galleryImage->id }}"
                                class="flex items-start gap-4 border border-zinc-200 dark:border-zinc-700 rounded-lg p-3">
                                <img src="{{ Storage::disk('public')->url($galleryImage->path) }}"
                                    alt="{{ $galleryImage->caption ?? 'Screenshot' }}"
                                    class="h-16 w-24 shrink-0 rounded object-cover">

                                <div class="min-w-0 flex-1">
                                    <flux:input wire:model="gallery_captions.{{ $galleryImage->id }}"
                                        wire:blur="saveCaption({{ $galleryImage->id }})" size="sm"
                                        placeholder="Caption — e.g. Queue module" />
                                </div>

                                <div class="flex shrink-0 items-center gap-1">
                                    <flux:button size="sm" variant="ghost" icon="arrow-up" aria-label="Move up"
                                        wire:click="moveImage({{ $galleryImage->id }}, 'up')"
                                        :disabled="$loop->first" />
                                    <flux:button size="sm" variant="ghost" icon="arrow-down" aria-label="Move down"
                                        wire:click="moveImage({{ $galleryImage->id }}, 'down')"
                                        :disabled="$loop->last" />
                                    <flux:button size="sm" variant="ghost" icon="trash" aria-label="Remove screenshot"
                                        wire:click="deleteImage({{ $galleryImage->id }})"
                                        wire:confirm="Remove this screenshot?" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>

        <div class="flex justify-end gap-3">
            <flux:button :href="route('admin.projects.index')" variant="ghost" wire:navigate>Cancel</flux:button>
            <flux:button type="submit" variant="primary">
                {{ $project?->exists ? 'Update Project' : 'Create Project' }}
            </flux:button>
        </div>
    </form>
</div>
