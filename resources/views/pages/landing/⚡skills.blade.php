<?php

use App\Models\Skill;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Skills')] class extends Component {
    public array $groupedSkills = [];

    public function mount(): void
    {
        $this->groupedSkills = Skill::ordered()->get()->groupBy('category')->map(fn($skills) => $skills->toArray())->toArray();
    }

    public function render()
    {
        return $this->view()->layout('layouts.landing');
    }
}; ?>

<div class="max-w-5xl mx-auto px-6 py-16">
    <div class="mb-12 text-center">
        <flux:heading size="xl" class="mb-3">Skills</flux:heading>
        <flux:subheading class="max-w-xl mx-auto">Technologies and tools I work with.</flux:subheading>
    </div>

    @if (count($groupedSkills) === 0)
        <div class="text-center py-20 text-zinc-400">
            <flux:icon name="wrench-screwdriver" class="size-12 mx-auto mb-4 opacity-40" />
            <p>No skills listed yet.</p>
        </div>
    @else
        <div class="space-y-12">
            @foreach ($groupedSkills as $category => $skills)
                <div>
                    <h2 class="text-lg font-semibold mb-6 flex items-center gap-3">
                        <span class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></span>
                        {{ $category }}
                        <span class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></span>
                    </h2>

                    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-4">
                        @foreach ($skills as $skill)
                            <div
                                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-4">
                                <div class="flex items-center gap-3 mb-3">
                                    @if ($skill['icon'])
                                        <img src="{{ $skill['icon'] }}" alt="{{ $skill['name'] }}"
                                            class="w-7 h-7 object-contain">
                                    @else
                                        <div
                                            class="w-7 h-7 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                                            <span
                                                class="text-xs font-bold text-blue-600 dark:text-blue-400">{{ strtoupper(substr($skill['name'], 0, 2)) }}</span>
                                        </div>
                                    @endif
                                    <span class="font-medium text-sm">{{ $skill['name'] }}</span>
                                    <span class="ms-auto text-xs text-zinc-400">{{ $skill['level'] }}%</span>
                                </div>

                                {{-- Progress bar --}}
                                <div class="h-1.5 bg-zinc-100 dark:bg-zinc-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-blue-500 to-purple-500 rounded-full transition-all duration-700"
                                        style="width: {{ $skill['level'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
