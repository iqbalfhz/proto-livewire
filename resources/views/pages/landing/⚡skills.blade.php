<?php

use App\Models\Skill;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Skills')] class extends Component {
    public function render()
    {
        $groupedSkills = Skill::ordered()
            ->select(['id', 'name', 'category'])
            ->get()
            ->groupBy('category')
            ->map(fn($skills) => $skills->toArray())
            ->toArray();

        return $this->view(['groupedSkills' => $groupedSkills])->layout('layouts.landing', [
            'description' => 'The languages, frameworks and tools I work with.',
        ]);
    }
}; ?>

<div>
    {{-- ═══════════════════════ MASTHEAD ═══════════════════════ --}}
    <section class="border-b border-rule">
        <div class="mx-auto max-w-5xl px-6">
            <x-landing.dateline left="What I work with" right="{{ collect($groupedSkills)->flatten(1)->count() }} entries" />

            <div class="grid items-end gap-8 py-14 md:grid-cols-12 md:py-20">
                <h1 class="display display-xl md:col-span-6">Skills</h1>
                <p class="text-lg leading-relaxed text-ink-soft md:col-span-6 md:pb-3">
                    The tools and skills I work with, grouped by area. Anything marked
                    basic is something I have used but am still learning.
                </p>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ THE CASE ═══════════════════════ --}}
    <section class="mx-auto max-w-5xl px-6 py-14 md:py-20">
        @if (count($groupedSkills) === 0)
            <div class="border-y border-rule py-28 text-center">
                <p class="display display-md text-ink-muted">No skills listed yet.</p>
            </div>
        @else
            @foreach ($groupedSkills as $category => $skills)
                <div class="mb-16 last:mb-0">
                    <div class="mb-8 flex items-baseline gap-4 border-b border-rule-strong pb-4">
                        <span class="index-number">{{ \App\Support\Text::index($loop->iteration) }}</span>
                        <h2 class="display display-md">{{ $category }}</h2>
                        <span class="h-px flex-1 bg-rule"></span>
                        <span class="label">{{ count($skills) }}</span>
                    </div>

                    <ul class="grid gap-x-12 sm:grid-cols-2">
                        @foreach ($skills as $skill)
                            <li class="display display-sm border-b border-rule py-3 transition-colors duration-300 hover:text-oxblood">
                                {{ $skill['name'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        @endif
    </section>

    {{-- ═══════════════════════ CTA ═══════════════════════ --}}
    <section class="border-t border-rule-strong">
        <div class="mx-auto max-w-5xl px-6 py-20 text-center md:py-24">
            <p class="display display-lg mx-auto mb-10 max-w-2xl">
                See what these look like <em>in practice.</em>
            </p>
            <div>
                <a href="{{ route('landing.projects') }}" wire:navigate class="btn-ink">
                    View the work <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </section>
</div>
