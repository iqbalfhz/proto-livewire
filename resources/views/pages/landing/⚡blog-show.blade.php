<?php

use App\Models\Post;
use Livewire\Attributes\Title;
use Livewire\Component;

new class extends Component {
    public Post $post;
    public array $relatedPosts = [];

    public function mount(string $slug): void
    {
        $this->post = Post::published()->where('slug', $slug)->firstOrFail();

        $this->relatedPosts = Post::published()->where('id', '!=', $this->post->id)->limit(3)->get()->toArray();
    }

    public function render()
    {
        return $this->view()->title($this->post->title)->layout('layouts.landing');
    }
}; ?>

<div class="max-w-3xl mx-auto px-6 py-16">
    {{-- Back link --}}
    <a href="{{ route('landing.blog') }}" wire:navigate
        class="inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-100 mb-8 transition">
        <flux:icon name="arrow-left" class="size-4" />
        Back to Blog
    </a>

    {{-- Thumbnail --}}
    @if ($post->thumbnail)
        <img src="{{ Storage::url($post->thumbnail) }}" alt="{{ $post->title }}"
            class="w-full h-72 object-cover rounded-2xl mb-8">
    @endif

    {{-- Meta --}}
    <div class="flex items-center gap-3 text-sm text-zinc-400 mb-4">
        <span>{{ $post->published_at?->format('F d, Y') }}</span>
    </div>

    <h1 class="text-3xl md:text-4xl font-bold tracking-tight mb-8">{{ $post->title }}</h1>

    {{-- Content --}}
    <div
        class="prose prose-zinc dark:prose-invert max-w-none text-zinc-700 dark:text-zinc-300 leading-relaxed whitespace-pre-wrap">
        {!! nl2br(e($post->content)) !!}
    </div>

    {{-- Related posts --}}
    @if (count($relatedPosts) > 0)
        <div class="mt-20 pt-10 border-t border-zinc-200 dark:border-zinc-700">
            <flux:heading class="mb-6">More Posts</flux:heading>
            <div class="grid md:grid-cols-3 gap-6">
                @foreach ($relatedPosts as $related)
                    <a href="{{ route('landing.blog.show', $related['slug']) }}" wire:navigate
                        class="group block bg-zinc-50 dark:bg-zinc-800 rounded-xl p-4 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition">
                        <p class="text-xs text-zinc-400 mb-1">
                            {{ \Carbon\Carbon::parse($related['published_at'])->format('M d, Y') }}</p>
                        <h3
                            class="text-sm font-medium group-hover:text-blue-600 dark:group-hover:text-blue-400 transition">
                            {{ $related['title'] }}</h3>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
