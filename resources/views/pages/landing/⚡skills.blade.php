<?php

use App\Models\Skill;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Skills')] class extends Component {
    public function render()
    {
        $groupedSkills = Skill::ordered()
            ->select(['id', 'name', 'icon', 'category', 'level'])
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
            <div class="flex items-center gap-4 border-b border-rule py-4">
                <span class="label">What I work with</span>
                <span class="h-px flex-1 bg-rule"></span>
                <span class="label">
                    {{ collect($groupedSkills)->flatten(1)->count() }} entries
                </span>
            </div>

            <div class="grid items-end gap-8 py-14 md:grid-cols-12 md:py-20">
                <h1 class="display display-xl md:col-span-6" data-reveal>Skills</h1>
                <p class="text-lg leading-relaxed text-ink-soft md:col-span-6 md:pb-3" data-reveal
                    data-reveal-delay="120">
                    The tools I reach for, and roughly how often. Depth matters more than
                    breadth — these are the ones I actually know well.
                </p>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ THE CASE ═══════════════════════ --}}
    <section class="mx-auto max-w-5xl px-6 py-14 md:py-20">
        @if (count($groupedSkills) === 0)
            <div class="border-y border-rule py-28 text-center" data-reveal>
                <p class="display display-md text-ink-muted">No skills listed yet.</p>
            </div>
        @else
            @foreach ($groupedSkills as $category => $skills)
                <div class="mb-16 last:mb-0" data-reveal>
                    <div class="mb-8 flex items-baseline gap-4 border-b border-rule-strong pb-4">
                        <span class="index-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <h2 class="display display-md">{{ $category }}</h2>
                        <span class="h-px flex-1 bg-rule"></span>
                        <span class="label">{{ count($skills) }}</span>
                    </div>

                    <div class="grid gap-x-12 gap-y-6 sm:grid-cols-2">
                        @foreach ($skills as $skill)
                            <div class="group border-b border-rule pb-4">
                                <div class="mb-2.5 flex items-baseline justify-between gap-4">
                                    <span
                                        class="display display-sm transition-colors duration-300 group-hover:text-oxblood">
                                        {{ $skill['name'] }}
                                    </span>
                                    <span class="label tabular-nums">{{ $skill['level'] }}</span>
                                </div>

                                {{-- Level drawn as a ruled measure, not a rounded pill --}}
                                <div class="h-px w-full bg-rule">
                                    <div class="h-px bg-oxblood transition-all duration-700 ease-out"
                                        style="width: {{ max(0, min(100, (int) $skill['level'])) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </section>

    {{-- ═══════════════════════ CTA ═══════════════════════ --}}
    <section class="border-t border-rule-strong">
        <div class="mx-auto max-w-5xl px-6 py-20 text-center md:py-24">
            <p class="display display-lg mx-auto mb-10 max-w-2xl" data-reveal>
                See what these look like <em>in practice.</em>
            </p>
            <div data-reveal data-reveal-delay="150">
                <a href="{{ route('landing.projects') }}" wire:navigate class="btn-ink">
                    View the work <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </section>
</div>
