<?php

use App\Models\SiteContent;
use App\Services\GithubContributionsService;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('About')] class extends Component {
    public array $about = [];
    public array $contributions = [];
    public int $totalContributions = 0;

    public function mount(GithubContributionsService $github): void
    {
        $this->about = SiteContent::group('about');

        $username = config('services.github.username');
        if ($username) {
            $data = $github->getContributions($username);
            $this->contributions = $data['weeks'];
            $this->totalContributions = $data['total'];
        }
    }

    public function render()
    {
        return $this->view()->layout('layouts.landing');
    }
}; ?>

<div class="max-w-5xl mx-auto px-6 py-16">

    {{-- About Section --}}
    <div class="grid md:grid-cols-5 gap-12 items-start mb-20">
        {{-- Photo --}}
        <div class="md:col-span-2 flex flex-col items-center">
            @if ($about['photo'] ?? null)
                <img src="{{ Storage::url($about['photo']) }}" alt="Profile photo"
                    class="w-48 h-48 rounded-2xl object-cover ring-4 ring-zinc-200 dark:ring-zinc-700 mb-6">
            @else
                <div
                    class="w-48 h-48 rounded-2xl bg-gradient-to-br from-blue-400 to-purple-600 flex items-center justify-center mb-6">
                    <flux:icon name="user" class="size-20 text-white/60" />
                </div>
            @endif

            {{-- Social links --}}
            <div class="flex gap-3">
                @if ($about['github_url'] ?? null)
                    <a href="{{ $about['github_url'] }}" target="_blank" rel="noopener"
                        class="w-9 h-9 bg-zinc-100 dark:bg-zinc-800 rounded-lg flex items-center justify-center hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
                        <flux:icon name="code-bracket" class="size-4 text-zinc-600 dark:text-zinc-400" />
                    </a>
                @endif
                @if ($about['linkedin_url'] ?? null)
                    <a href="{{ $about['linkedin_url'] }}" target="_blank" rel="noopener"
                        class="w-9 h-9 bg-zinc-100 dark:bg-zinc-800 rounded-lg flex items-center justify-center hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
                        <flux:icon name="link" class="size-4 text-zinc-600 dark:text-zinc-400" />
                    </a>
                @endif
                @if ($about['twitter_url'] ?? null)
                    <a href="{{ $about['twitter_url'] }}" target="_blank" rel="noopener"
                        class="w-9 h-9 bg-zinc-100 dark:bg-zinc-800 rounded-lg flex items-center justify-center hover:bg-zinc-200 dark:hover:bg-zinc-700 transition">
                        <flux:icon name="at-symbol" class="size-4 text-zinc-600 dark:text-zinc-400" />
                    </a>
                @endif
            </div>
        </div>

        {{-- Bio --}}
        <div class="md:col-span-3">
            <flux:heading size="xl" class="mb-2">
                {{ $about['name'] ?? config('app.name') }}
            </flux:heading>
            <p class="text-blue-600 dark:text-blue-400 font-medium mb-6">
                {{ $about['role'] ?? 'Full Stack Developer' }}
            </p>
            <div class="text-zinc-600 dark:text-zinc-400 leading-relaxed space-y-4 text-sm whitespace-pre-wrap">
                {!! nl2br(e($about['bio'] ?? 'Hello! I am a passionate developer who loves building things for the web.')) !!}
            </div>

            @if ($about['resume_url'] ?? null)
                <div class="mt-8">
                    <flux:button :href="$about['resume_url']" target="_blank" variant="primary" icon="arrow-down-tray">
                        Download Resume
                    </flux:button>
                </div>
            @endif
        </div>
    </div>

    {{-- GitHub Contributions --}}
    @if (count($contributions) > 0)
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-2xl p-6 md:p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <flux:heading class="mb-1">GitHub Contributions</flux:heading>
                    <flux:subheading>{{ number_format($totalContributions) }} contributions in the last year
                    </flux:subheading>
                </div>
                @if (config('services.github.username'))
                    <a href="https://github.com/{{ config('services.github.username') }}" target="_blank" rel="noopener"
                        class="text-sm text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-100 transition flex items-center gap-1">
                        @{{ config('services.github.username') }}
                        <flux:icon name="arrow-top-right-on-square" class="size-3.5" />
                    </a>
                @endif
            </div>

            {{-- Heatmap grid --}}
            <div class="overflow-x-auto pb-2">
                <div class="flex gap-1 min-w-max">
                    @foreach ($contributions as $week)
                        <div class="flex flex-col gap-1">
                            @foreach ($week['contributionDays'] as $day)
                                @php
                                    $count = $day['contributionCount'];
                                    $colorClass = match (true) {
                                        $count === 0 => 'bg-zinc-100 dark:bg-zinc-800',
                                        $count <= 3 => 'bg-green-200 dark:bg-green-900',
                                        $count <= 9 => 'bg-green-400 dark:bg-green-700',
                                        $count <= 19 => 'bg-green-600 dark:bg-green-500',
                                        default => 'bg-green-800 dark:bg-green-400',
                                    };
                                @endphp
                                <div class="w-3 h-3 rounded-sm {{ $colorClass }} cursor-pointer transition hover:opacity-80"
                                    title="{{ $day['date'] }}: {{ $count }} contribution{{ $count !== 1 ? 's' : '' }}"
                                    x-data
                                    x-tooltip.raw="{{ $day['date'] }}: {{ $count }} contribution{{ $count !== 1 ? 's' : '' }}">
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Legend --}}
            <div class="flex items-center gap-2 mt-4 text-xs text-zinc-400">
                <span>Less</span>
                <div class="w-3 h-3 rounded-sm bg-zinc-100 dark:bg-zinc-800"></div>
                <div class="w-3 h-3 rounded-sm bg-green-200 dark:bg-green-900"></div>
                <div class="w-3 h-3 rounded-sm bg-green-400 dark:bg-green-700"></div>
                <div class="w-3 h-3 rounded-sm bg-green-600 dark:bg-green-500"></div>
                <div class="w-3 h-3 rounded-sm bg-green-800 dark:bg-green-400"></div>
                <span>More</span>
            </div>
        </div>
    @endif

</div>
