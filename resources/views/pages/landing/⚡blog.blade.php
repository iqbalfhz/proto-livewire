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

    #[Url]
    public string $tag = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTag(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'tag');
        $this->resetPage();
    }

    public function render()
    {
        $posts = Post::published()
            ->when(
                $this->search,
                fn($q) => $q->where(
                    fn($q) => $q->where('title', 'like', "%{$this->search}%")->orWhere('excerpt', 'like', "%{$this->search}%"),
                ),
            )
            ->when($this->tag !== '', fn($q) => $q->whereJsonContains('tags', $this->tag))
            ->paginate(12);

        $tags = Post::published()->pluck('tags')->filter()->flatten()->countBy()->sortDesc()->keys()->map(fn($tag) => (string) $tag)->take(12)->all();

        return $this->view(['posts' => $posts, 'tags' => $tags])->layout('layouts.landing', [
            'description' => 'Articles, notes and write-ups on building for the web.',
        ]);
    }
}; ?>

<div>
    {{-- ═══════════════════════ MASTHEAD ═══════════════════════ --}}
    <section class="border-b border-rule">
        <div class="mx-auto max-w-6xl px-6">
            <x-landing.dateline left="Articles" :right="$posts->total() . ' ' . Str::plural('piece', $posts->total())" />

            <div class="grid items-end gap-8 py-14 md:grid-cols-12 md:py-20">
                <h1 class="display display-xl md:col-span-6">Blog</h1>

                <div class="md:col-span-6 md:pb-3">
                    <p class="mb-7 text-lg leading-relaxed text-ink-soft">
                        Notes on building for the web — what worked, what broke, and what
                        I'd do differently next time.
                    </p>

                    {{-- Search, set as a ruled line rather than a boxed field --}}
                    <div class="relative border-b border-rule-strong pb-2">
                        <input type="search" wire:model.live.debounce.400ms="search" placeholder="Search the archive…"
                            aria-label="Search articles"
                            class="w-full border-0 bg-transparent p-0 pr-8 font-mono text-sm text-ink placeholder:text-ink-muted focus:outline-none focus:ring-0">
                        <span class="absolute right-0 top-0 text-ink-muted" wire:loading.remove
                            wire:target="search">↗</span>
                        <span class="label absolute right-0 top-0" wire:loading wire:target="search">···</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ TOPICS ═══════════════════════ --}}
    @if (count($tags) > 0)
        <section class="border-b border-rule">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-3 px-6 py-4">
                <span class="label shrink-0 text-ink!">Topics</span>
                <button type="button" wire:click="$set('tag', '')"
                    class="label link-sweep {{ $tag === '' ? 'link-retract text-ink!' : '' }} hover:text-ink! transition-colors">
                    All
                </button>
                @foreach ($tags as $tagOption)
                    <button type="button" wire:click="$set('tag', @js($tagOption))"
                        class="label link-sweep {{ $tag === $tagOption ? 'link-retract text-ink!' : '' }} hover:text-ink! transition-colors">
                        {{ $tagOption }}
                    </button>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ═══════════════════════ THE ARCHIVE ═══════════════════════ --}}
    <section class="mx-auto max-w-6xl px-6 py-14 md:py-20">
        @if ($posts->count() === 0)
            <div class="border-y border-rule py-28 text-center">
                <p class="display display-md mb-3 text-ink-muted">No posts found.</p>
                @if ($search !== '' || $tag !== '')
                    <button type="button" wire:click="clearFilters"
                        class="label link-sweep link-retract hover:text-ink! transition-colors">
                        Clear filters
                    </button>
                @endif
            </div>
        @else
            <div class="border-t border-rule-strong">
                @foreach ($posts as $i => $post)
                    <a href="{{ route('landing.blog.show', $post->slug) }}" wire:navigate
                        class="index-row group flex flex-col gap-4 py-8 sm:flex-row sm:items-baseline sm:gap-8 sm:py-10"
                        @if ($post->thumbnail) data-preview="{{ Storage::disk('public')->url($post->thumbnail) }}" @endif>
                        <span class="label w-28 shrink-0">
                            {{ $post->published_at?->format('d M Y') ?? '—' }}
                        </span>

                        <span class="index-title min-w-0 flex-1">
                            <span
                                class="display display-sm block transition-colors duration-300 group-hover:text-oxblood">
                                {{ $post->title }}
                            </span>
                            @if ($post->excerpt)
                                <span class="mt-2 block max-w-2xl text-sm leading-relaxed text-ink-muted">
                                    {{ Str::limit($post->excerpt, 160) }}
                                </span>
                            @endif
                        </span>

                        <span
                            class="hidden shrink-0 text-ink-muted transition-all duration-300 group-hover:translate-x-1 group-hover:text-oxblood sm:block"
                            aria-hidden="true">↗</span>
                    </a>
                @endforeach
            </div>

            @if ($posts->hasPages())
                <div class="mt-12 border-t border-rule pt-6">
                    {{ $posts->links() }}
                </div>
            @endif
        @endif
    </section>
</div>
