<?php

use App\Models\Post;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public ?Post $post = null;

    public string $title = '';
    public string $slug = '';
    public string $excerpt = '';
    public string $content = '';
    public bool $is_published = false;
    public $thumbnail = null;
    public ?string $existing_thumbnail = null;

    public function mount(?Post $post = null): void
    {
        if ($post?->exists) {
            $this->post = $post;
            $this->title = $post->title;
            $this->slug = $post->slug;
            $this->excerpt = $post->excerpt ?? '';
            $this->content = $post->content;
            $this->is_published = $post->is_published;
            $this->existing_thumbnail = $post->thumbnail;
        }
    }

    public function updatedTitle(): void
    {
        if (!$this->post?->exists) {
            $this->slug = \Illuminate\Support\Str::slug($this->title);
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'is_published' => ['boolean'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
        ]);

        $thumbnailPath = $this->existing_thumbnail;
        if ($this->thumbnail) {
            $thumbnailPath = $this->thumbnail->store('posts', 'public');
        }

        $data = [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'is_published' => $validated['is_published'],
            'thumbnail' => $thumbnailPath,
        ];

        if ($this->post?->exists) {
            $this->post->update($data);
            if ($validated['is_published'] && !$this->post->published_at) {
                $this->post->update(['published_at' => now()]);
            }
            Flux::toast(variant: 'success', text: 'Post updated.');
        } else {
            $post = Post::create(
                array_merge($data, [
                    'published_at' => $validated['is_published'] ? now() : null,
                ]),
            );
            Flux::toast(variant: 'success', text: 'Post created.');
            $this->redirectRoute('admin.blog.edit', ['post' => $post], navigate: true);
        }
    }

    public function render()
    {
        $title = $this->post?->exists ? 'Edit Post' : 'New Post';
        return view('pages.admin.blog-form')->title($title)->layout('layouts.admin');
    }
}; ?>

<div class="max-w-3xl">
    <div class="flex items-center gap-3 mb-8">
        <flux:button :href="route('admin.blog.index')" variant="ghost" icon="arrow-left" size="sm" wire:navigate />
        <div>
            <flux:heading size="xl">{{ $post?->exists ? 'Edit Post' : 'New Post' }}</flux:heading>
            <flux:subheading>{{ $post?->exists ? 'Update your blog post.' : 'Write a new blog post.' }}
            </flux:subheading>
        </div>
    </div>

    <form wire:submit="save" class="space-y-5">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 space-y-5">
            <flux:input wire:model.live="title" label="Title" placeholder="Post title..." required />
            <flux:input wire:model="slug" label="Slug" placeholder="post-slug" required />
            <flux:textarea wire:model="excerpt" label="Excerpt" rows="2" placeholder="Short description..." />
            <flux:textarea wire:model="content" label="Content" rows="16"
                placeholder="Write your post content here..." required />

            {{-- Thumbnail --}}
            <div>
                <flux:label>Thumbnail</flux:label>
                <div class="mt-2 flex items-center gap-4">
                    @if ($thumbnail)
                        <img src="{{ $thumbnail->temporaryUrl() }}"
                            class="w-24 h-16 object-cover rounded-lg ring-2 ring-blue-500" alt="Preview">
                    @elseif($existing_thumbnail)
                        <img src="{{ Storage::url($existing_thumbnail) }}"
                            class="w-24 h-16 object-cover rounded-lg ring-2 ring-zinc-300 dark:ring-zinc-600"
                            alt="Current thumbnail">
                    @endif
                    <flux:input type="file" wire:model="thumbnail" accept="image/*" />
                </div>
            </div>

            <div class="flex items-center gap-3">
                <flux:checkbox wire:model="is_published" id="is_published" />
                <flux:label for="is_published">Published</flux:label>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <flux:button :href="route('admin.blog.index')" variant="ghost" wire:navigate>Cancel</flux:button>
            <flux:button type="submit" variant="primary">
                {{ $post?->exists ? 'Update Post' : 'Create Post' }}
            </flux:button>
        </div>
    </form>
</div>
