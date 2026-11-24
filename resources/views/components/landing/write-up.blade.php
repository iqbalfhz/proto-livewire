@props(['label', 'text', 'dropcap' => false])

<section class="mx-auto max-w-5xl px-6 py-12 md:py-16">
    <div class="grid gap-10 md:grid-cols-12">
        <div class="md:col-span-3">
            <h2 class="label md:sticky md:top-32">{{ $label }}</h2>
        </div>
        <div @class(['paragraphs text-lg leading-[1.8] text-ink-soft md:col-span-9', 'dropcap' => $dropcap])>
            {!! \App\Support\Text::paragraphs($text) !!}
        </div>
    </div>
</section>
