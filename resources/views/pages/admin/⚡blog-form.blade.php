<?php

use App\Models\Post;
use App\Support\Text;
use App\Support\UploadedImages;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public ?Post $post = null;

    public string $title = '';
    public string $slug = '';
    public string $excerpt = '';
    public string $tags_input = '';
    public string $content = '';
    public bool $is_published = false;
    public $thumbnail = null;

    #[Locked]
    public ?string $existing_thumbnail = null;

    public function mount(?Post $post = null): void
    {
        if ($post?->exists) {
            $this->post = $post;
            $this->title = $post->title;
            $this->slug = $post->slug;
            $this->excerpt = $post->excerpt ?? '';
            $this->tags_input = implode(', ', (array) $post->tags);
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
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('posts', 'slug')->ignore($this->post?->id)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'tags_input' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_published' => ['boolean'],
            'thumbnail' => ['nullable', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'],
        ]);

        $thumbnailPath = $this->existing_thumbnail;
        if ($this->thumbnail) {
            $thumbnailPath = $this->thumbnail->store('posts', 'public');
            UploadedImages::replace($this->existing_thumbnail, $thumbnailPath);
            $this->existing_thumbnail = $thumbnailPath;
        }

        $data = [
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'],
            'tags' => Text::commaList($this->tags_input),
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
            Post::create(
                array_merge($data, [
                    'published_at' => $validated['is_published'] ? now() : null,
                ]),
            );
            Flux::toast(variant: 'success', text: 'Post created.');
        }

        $this->redirectRoute('admin.blog.index', navigate: true);
    }

    public function render()
    {
        $title = $this->post?->exists ? 'Edit Post' : 'New Post';
        return $this->view()->title($title)->layout('layouts.admin');
    }
}; ?>

<div>
    <x-admin.page-header :back="route('admin.blog.index')" :title="$post?->exists ? 'Edit Post' : 'New Post'"
        :subtitle="$post?->exists ? 'Update your blog post.' : 'Write a new blog post.'" />

    <form wire:submit="save" class="space-y-5">
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6 space-y-5">
            <flux:input wire:model.live="title" label="Title" placeholder="Post title..." required />
            <flux:input wire:model="slug" label="Slug" placeholder="post-slug" required />
            <flux:textarea wire:model="excerpt" label="Excerpt" rows="2" placeholder="Short description..." />
            <flux:input wire:model="tags_input" label="Tags (comma-separated)" placeholder="Laravel, Performance" />

            {{-- Quill Editor --}}
            <div>
                <flux:label class="mb-2 block">Content</flux:label>
                <div wire:ignore id="quill-wrapper" data-upload-url="{{ route('admin.upload-image') }}"
                    data-csrf="{{ csrf_token() }}">
                    <div id="quill-editor"></div>
                    <div id="quill-wordcount"
                        style="text-align:right;font-size:11px;color:#71717a;padding:4px 6px;border:1px solid #d4d4d8;border-top:none;border-radius:0 0 4px 4px;">
                    </div>
                </div>
                @error('content')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Thumbnail --}}
            <div>
                <flux:label>Thumbnail</flux:label>
                <div class="mt-2 flex items-center gap-4">
                    @if ($thumbnail)
                        <img src="{{ $thumbnail->temporaryUrl() }}"
                            class="w-24 h-16 object-cover rounded-lg ring-2 ring-blue-500" alt="Preview">
                    @elseif($existing_thumbnail)
                        <img src="{{ Storage::disk('public')->url($existing_thumbnail) }}"
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

<script>
    window.initPostEditor($wire);
</script>
