<?php

use App\Models\Post;
use App\Models\SiteContent;
use App\Support\ArticleBody;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

new class extends Component {
    public Post $post;

    public function mount(string $slug): void
    {
        $this->post = Post::published()->where('slug', $slug)->firstOrFail();
    }

    public function render()
    {
        $publishedAt = $this->post->published_at;
        $olderPost = $publishedAt ? Post::published()->where('published_at', '<', $publishedAt)->first(['id', 'title', 'slug']) : null;
        $newerPost = $publishedAt
            ? Post::where('is_published', true)->where('published_at', '>', $publishedAt)->orderBy('published_at')->first(['id', 'title', 'slug'])
            : null;

        $relatedPosts = Post::published()
            ->select(['id', 'title', 'slug', 'published_at'])
            ->whereKeyNot(array_filter([$this->post->getKey(), $olderPost?->getKey(), $newerPost?->getKey()]))
            ->limit(3)
            ->get()
            ->toArray();

        $url = route('landing.blog.show', $this->post->slug);
        $image = $this->post->thumbnail ? Storage::disk('public')->url($this->post->thumbnail) : null;

        return $this->view([
            'body' => ArticleBody::from($this->post->content),
            'olderPost' => $olderPost,
            'newerPost' => $newerPost,
            'relatedPosts' => $relatedPosts,
        ])
            ->title($this->post->title)
            ->layout('layouts.landing', [
                'description' => $this->post->excerpt ?: strip_tags($this->post->content),
                'ogType' => 'article',
                'ogImage' => route('og.image', ['type' => 'posts', 'slug' => $this->post->slug, 'v' => $this->post->updated_at?->timestamp]),
                'jsonLd' => array_filter([
                    '@context' => 'https://schema.org',
                    '@type' => 'BlogPosting',
                    'headline' => $this->post->title,
                    'mainEntityOfPage' => $url,
                    'url' => $url,
                    'image' => $image,
                    'datePublished' => $this->post->published_at?->toAtomString(),
                    'dateModified' => $this->post->updated_at?->toAtomString(),
                    'author' => [
                        '@type' => 'Person',
                        'name' => SiteContent::get('about', 'name', config('app.name')),
                    ],
                ]),
            ]);
    }
}; ?>

<div>
    {{-- Reading progress, drawn as a rule that fills --}}
    <div class="fixed inset-x-0 top-0 z-50 h-0.5 origin-left scale-x-0 bg-oxblood" data-reading-progress></div>

    {{-- ═══════════════════════ HEADER ═══════════════════════ --}}
    <article>
        <header class="border-b border-rule">
            <div class="mx-auto max-w-3xl px-6">
                <div class="flex items-center gap-4 border-b border-rule py-4">
                    <a href="{{ route('landing.blog') }}" wire:navigate
                        class="label link-sweep shrink-0 hover:text-ink! transition-colors">
                        ← All articles
                    </a>
                    <span class="h-px flex-1 bg-rule"></span>
                    <span class="label">
                        {{ max(1, (int) ceil(str_word_count(strip_tags($post->content)) / 200)) }} min read
                    </span>
                </div>

                <div class="py-14 md:py-20">
                    <p class="label mb-6">
                        {{ $post->published_at?->format('d F Y') }}
                    </p>
                    <h1 class="display display-lg">{{ $post->title }}</h1>

                    @if ($post->excerpt)
                        <p class="mt-8 max-w-2xl border-l-2 border-oxblood pl-5 text-lg leading-relaxed text-ink-soft">
                            {{ $post->excerpt }}
                        </p>
                    @endif

                    @if (!empty($post->tags))
                        <div class="mt-8 flex flex-wrap gap-x-5 gap-y-2">
                            @foreach ($post->tags as $tag)
                                <a href="{{ route('landing.blog', ['tag' => $tag]) }}" wire:navigate
                                    class="label link-sweep hover:text-ink! transition-colors">{{ $tag }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </header>

        {{-- ═══════════════════════ PLATE ═══════════════════════ --}}
        @if ($post->thumbnail)
            <div class="border-b border-rule bg-paper-sunk">
                <div class="mx-auto max-w-4xl px-6 py-12">
                    <img src="{{ Storage::disk('public')->url($post->thumbnail) }}" alt="{{ $post->title }}"
                        class="w-full border border-rule-strong object-cover">
                </div>
            </div>
        @endif

        {{-- ═══════════════════════ BODY ═══════════════════════ --}}
        @php($hasOutline = count($body->headings) >= 2)
        <div @class([
            'mx-auto px-6 py-16 md:py-20',
            'max-w-6xl lg:grid lg:grid-cols-12 lg:gap-12' => $hasOutline,
            'max-w-3xl' => !$hasOutline,
        ])>
            @if ($hasOutline)
                <nav aria-label="On this page" class="mb-12 border-y border-rule py-5 lg:col-span-3 lg:mb-0 lg:border-0 lg:py-0">
                    <div class="lg:sticky lg:top-32">
                        <p class="label mb-4">On this page</p>
                        <ol class="space-y-2.5">
                            @php($topLevel = collect($body->headings)->min('level'))
                            @foreach ($body->headings as $heading)
                                <li @class(['pl-4' => $heading['level'] > $topLevel])>
                                    <a href="#{{ $heading['id'] }}"
                                        class="text-sm leading-snug text-ink-muted transition-colors hover:text-oxblood">
                                        {{ $heading['text'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </nav>
            @endif

            <div @class(['lg:col-span-8 lg:col-start-5' => $hasOutline])>
                <div class="dropcap prose prose-editorial max-w-none">
                    {!! $body->html !!}
                </div>

                <div class="mt-16 flex items-center justify-between gap-4 border-t border-rule pt-6">
                    <span class="label">End of article</span>
                    <a href="{{ route('landing.contact') }}" wire:navigate
                        class="label link-sweep hover:text-ink! transition-colors">
                        Got thoughts? Say hi →
                    </a>
                </div>

                @if ($olderPost || $newerPost)
                    <nav aria-label="More from the archive" class="mt-10 grid gap-8 sm:grid-cols-2">
                        @if ($olderPost)
                            <a href="{{ route('landing.blog.show', $olderPost->slug) }}" wire:navigate class="group block">
                                <span class="label mb-2 block">← Older</span>
                                <span class="display display-sm block transition-colors group-hover:text-oxblood">{{ $olderPost->title }}</span>
                            </a>
                        @endif
                        @if ($newerPost)
                            <a href="{{ route('landing.blog.show', $newerPost->slug) }}" wire:navigate
                                class="group block sm:col-start-2 sm:text-right">
                                <span class="label mb-2 block">Newer →</span>
                                <span class="display display-sm block transition-colors group-hover:text-oxblood">{{ $newerPost->title }}</span>
                            </a>
                        @endif
                    </nav>
                @endif
            </div>
        </div>
    </article>

    {{-- ═══════════════════════ MORE READING ═══════════════════════ --}}
    @if (count($relatedPosts) > 0)
        <section class="border-t border-rule-strong">
            <div class="mx-auto max-w-3xl px-6 py-16 md:py-20">
                <div class="mb-8 flex items-baseline gap-4">
                    <span class="index-number">→</span>
                    <h2 class="display display-md">More Articles</h2>
                </div>

                <div class="border-t border-rule">
                    @foreach ($relatedPosts as $i => $related)
                        <a href="{{ route('landing.blog.show', $related['slug']) }}" wire:navigate
                            class="index-row group flex items-baseline gap-5 py-6 sm:gap-8">
                            <span class="label w-24 shrink-0">
                                {{ $related['published_at'] ? \Carbon\Carbon::parse($related['published_at'])->format('d M Y') : '—' }}
                            </span>
                            <span class="index-title min-w-0 flex-1">
                                <span
                                    class="display display-sm block transition-colors duration-300 group-hover:text-oxblood">
                                    {{ $related['title'] }}
                                </span>
                            </span>
                            <span
                                class="shrink-0 text-ink-muted transition-all duration-300 group-hover:translate-x-1 group-hover:text-oxblood"
                                aria-hidden="true">↗</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>
