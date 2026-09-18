<?php

use App\Models\Post;
use App\Models\SiteContent;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;

new class extends Component {
    public Post $post;

    public function mount(string $slug): void
    {
        $this->post = Post::published()->where('slug', $slug)->firstOrFail();
    }

    public function render()
    {
        $relatedPosts = Post::published()
            ->select(['id', 'title', 'slug', 'published_at'])
            ->whereKeyNot($this->post->getKey())
            ->limit(3)
            ->get()
            ->toArray();

        $url = route('landing.blog.show', $this->post->slug);
        $image = $this->post->thumbnail ? Storage::disk('public')->url($this->post->thumbnail) : null;

        return $this->view(['relatedPosts' => $relatedPosts])
            ->title($this->post->title)
            ->layout('layouts.landing', [
                'description' => $this->post->excerpt ?: strip_tags($this->post->content),
                'ogType' => 'article',
                'ogImage' => $image,
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
                    <p class="label mb-6" data-reveal>
                        {{ $post->published_at?->format('d F Y') }}
                    </p>
                    <h1 class="display display-lg" data-reveal data-reveal-delay="80">{{ $post->title }}</h1>

                    @if ($post->excerpt)
                        <p class="mt-8 max-w-2xl border-l-2 border-oxblood pl-5 text-lg leading-relaxed text-ink-soft"
                            data-reveal data-reveal-delay="160">
                            {{ $post->excerpt }}
                        </p>
                    @endif
                </div>
            </div>
        </header>

        {{-- ═══════════════════════ PLATE ═══════════════════════ --}}
        @if ($post->thumbnail)
            <div class="border-b border-rule bg-paper-sunk">
                <div class="mx-auto max-w-4xl px-6 py-12">
                    <img src="{{ Storage::disk('public')->url($post->thumbnail) }}" alt="{{ $post->title }}"
                        class="w-full border border-rule-strong object-cover" data-reveal>
                </div>
            </div>
        @endif

        {{-- ═══════════════════════ BODY ═══════════════════════ --}}
        <div class="mx-auto max-w-3xl px-6 py-16 md:py-20">
            <div class="dropcap prose prose-editorial max-w-none">
                {!! $post->content !!}
            </div>

            <div class="mt-16 flex items-center justify-between gap-4 border-t border-rule pt-6">
                <span class="label">End of article</span>
                <a href="{{ route('landing.contact') }}" wire:navigate
                    class="label link-sweep hover:text-ink! transition-colors">
                    Got thoughts? Say hi →
                </a>
            </div>
        </div>
    </article>

    {{-- ═══════════════════════ MORE READING ═══════════════════════ --}}
    @if (count($relatedPosts) > 0)
        <section class="border-t border-rule-strong">
            <div class="mx-auto max-w-3xl px-6 py-16 md:py-20">
                <div class="mb-8 flex items-baseline gap-4" data-reveal>
                    <span class="index-number">→</span>
                    <h2 class="display display-md">More Articles</h2>
                </div>

                <div class="border-t border-rule">
                    @foreach ($relatedPosts as $i => $related)
                        <a href="{{ route('landing.blog.show', $related['slug']) }}" wire:navigate
                            class="index-row group flex items-baseline gap-5 py-6 sm:gap-8" data-reveal
                            data-reveal-delay="{{ $i * 70 }}">
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
