<?php

use App\Models\Post;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Blog Posts')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function togglePublish(int $id): void
    {
        $post = Post::findOrFail($id);
        $post->update([
            'is_published' => !$post->is_published,
            'published_at' => !$post->is_published ? now() : $post->published_at,
        ]);
        Flux::toast(variant: 'success', text: $post->fresh()->is_published ? 'Post published.' : 'Post unpublished.');
    }

    public function delete(int $id): void
    {
        Post::findOrFail($id)->delete();
        Flux::toast(variant: 'success', text: 'Post deleted.');
    }

    public function render()
    {
        $posts = Post::when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))->latest()->paginate(15);

        return $this->view(['posts' => $posts])->layout('layouts.admin');
    }
}; ?>

<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl">Blog Posts</flux:heading>
            <flux:subheading>Manage your blog content.</flux:subheading>
        </div>
        <flux:button :href="route('admin.blog.create')" variant="primary" icon="plus" wire:navigate>New Post
        </flux:button>
    </div>

    <div class="mb-5 max-w-xs">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search posts..." icon="magnifying-glass" />
    </div>

    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden">
        @if ($posts->isEmpty())
            <div class="py-16 text-center text-zinc-400">
                <flux:icon name="document-text" class="size-10 mx-auto mb-3 opacity-40" />
                <p>No posts yet. <a href="{{ route('admin.blog.create') }}" wire:navigate
                        class="text-blue-500 hover:underline">Create one!</a></p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="border-b border-zinc-200 dark:border-zinc-700 text-xs text-zinc-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Title</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Status</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Date</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @foreach ($posts as $post)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                            <td class="px-4 py-3 font-medium max-w-xs truncate">{{ $post->title }}</td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                @if ($post->is_published)
                                    <flux:badge color="green" size="sm">Published</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Draft</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-zinc-500 hidden md:table-cell">
                                {{ $post->published_at?->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1">
                                    <flux:tooltip :content="$post->is_published ? 'Unpublish' : 'Publish'">
                                        <flux:button wire:click="togglePublish({{ $post->id }})" variant="ghost"
                                            size="sm" :icon="$post->is_published ? 'eye-slash' : 'eye'" />
                                    </flux:tooltip>
                                    <flux:button :href="route('admin.blog.edit', $post)" variant="ghost" size="sm"
                                        icon="pencil" wire:navigate />
                                    <flux:modal.trigger :name="'delete-post-'.$post->id">
                                        <flux:button variant="ghost" size="sm" icon="trash"
                                            class="text-red-500 hover:text-red-600" />
                                    </flux:modal.trigger>
                                </div>

                                <flux:modal :name="'delete-post-'.$post->id" class="max-w-sm">
                                    <div class="space-y-4">
                                        <flux:heading>Delete Post?</flux:heading>
                                        <flux:subheading>This will permanently delete "{{ $post->title }}".
                                        </flux:subheading>
                                        <div class="flex justify-end gap-2">
                                            <flux:modal.close>
                                                <flux:button variant="filled">Cancel</flux:button>
                                            </flux:modal.close>
                                            <flux:button wire:click="delete({{ $post->id }})" variant="danger">
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
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
