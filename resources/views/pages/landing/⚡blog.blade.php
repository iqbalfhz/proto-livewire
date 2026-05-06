<?php

use App\Models\Post;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Blog')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $posts = Post::published()->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%")->orWhere('excerpt', 'like', "%{$this->search}%"))->paginate(9);

        return view('pages.landing.blog', ['posts' => $posts])->layout('layouts.landing');
    }
}; ?>

<div class="max-w-5xl mx-auto px-6 py-16">
    <div class="mb-12 text-center">
        <flux:heading size="xl" class="mb-3">Blog</flux:heading>
        <flux:subheading class="max-w-xl mx-auto">Thoughts, tutorials, and notes on web development.</flux:subheading>
    </div>

    <div class="max-w-md mx-auto mb-10">
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search posts..." icon="magnifying-glass" />
    </div>

    @if ($posts->isEmpty())
        <div class="text-center py-20 text-zinc-400">
            <flux:icon name="document-text" class="size-12 mx-auto mb-4 opacity-40" />
            <p>No posts found.</p>
        </div>
    @else
        <div class="grid md:grid-cols-3 gap-6">
            @foreach ($posts as $post)
                <a href="{{ route('landing.blog.show', $post->slug) }}" wire:navigate
                    class="group block bg-white dark:bg-zinc-900 rounded-xl border border-zinc-200 dark:border-zinc-700 overflow-hidden hover:border-blue-400 dark:hover:border-blue-500 transition">
                    @if ($post->thumbnail)
                        <img src="{{ Storage::url($post->thumbnail) }}" alt="{{ $post->title }}"
                            class="w-full h-44 object-cover group-hover:scale-105 transition duration-300">
                    @else
                        <div
                            class="w-full h-44 bg-gradient-to-br from-zinc-200 to-zinc-300 dark:from-zinc-700 dark:to-zinc-800 flex items-center justify-center">
                            <flux:icon name="document-text" class="size-10 text-zinc-400" />
                        </div>
                    @endif
                    <div class="p-5">
                        <p class="text-xs text-zinc-400 mb-2">{{ $post->published_at?->format('M d, Y') }}</p>
                        <h2
                            class="font-semibold text-base mb-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                            {{ $post->title }}</h2>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 line-clamp-3">{{ $post->excerpt }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $posts->links() }}
        </div>
    @endif
</div>
