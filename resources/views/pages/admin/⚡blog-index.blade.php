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
        $publishing = !$post->is_published;

        $post->update([
            'is_published' => $publishing,
            // Keep the original publication date — re-publishing is not re-dating.
            'published_at' => $publishing ? $post->published_at ?? now() : $post->published_at,
        ]);

        Flux::toast(variant: 'success', text: $publishing ? 'Post published.' : 'Post unpublished.');
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
    <x-admin.page-header title="Blog Posts" subtitle="Manage your blog content">
        <flux:button :href="route('admin.blog.create')" variant="primary" icon="plus" wire:navigate>New Post
        </flux:button>
    </x-admin.page-header>

    <div class="mb-5 max-w-xs">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search posts..." icon="magnifying-glass" />
    </div>

    <div class="admin-panel overflow-hidden">
        @if ($posts->isEmpty())
            <x-admin.empty-state icon="document-text">No posts yet. <a href="{{ route('admin.blog.create') }}" wire:navigate class="text-oxblood underline-offset-4 hover:underline">Create one!</a></x-admin.empty-state>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th class="hidden md:table-cell">Status</th>
                        <th class="hidden md:table-cell">Date</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($posts as $post)
                        <tr>
                            <td class="font-medium max-w-xs truncate">{{ $post->title }}</td>
                            <td class="hidden md:table-cell">
                                @if ($post->is_published)
                                    <flux:badge color="green" size="sm">Published</flux:badge>
                                @else
                                    <flux:badge color="zinc" size="sm">Draft</flux:badge>
                                @endif
                            </td>
                            <td class="text-zinc-500 hidden md:table-cell">
                                {{ $post->published_at?->format('M d, Y') ?? '—' }}
                            </td>
                            <td>
                                <div class="flex justify-end gap-1">
                                    <flux:tooltip :content="$post->is_published ? 'Unpublish' : 'Publish'">
                                        <flux:button wire:click="togglePublish({{ $post->id }})" variant="ghost"
                                            size="sm" :icon="$post->is_published ? 'eye-slash' : 'eye'"
                                            :aria-label="$post->is_published ? 'Unpublish' : 'Publish'" />
                                    </flux:tooltip>
                                    <flux:button :href="route('admin.blog.edit', $post)" variant="ghost" size="sm"
                                        icon="pencil" aria-label="Edit post" wire:navigate />
                                    <x-admin.delete-trigger :name="'delete-post-'.$post->id" label="Delete post" />
                                </div>

                                <x-admin.confirm-delete :name="'delete-post-'.$post->id" heading="Delete Post?" :action="'delete('.$post->id.')'">
                                    This will permanently delete "{{ $post->title }}".
                                </x-admin.confirm-delete>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if ($posts->hasPages())
                <div class="admin-panel-footer">{{ $posts->links() }}</div>
            @endif
        @endif
    </div>
</div>
