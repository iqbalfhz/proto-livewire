<?php

use App\Enums\ExperienceType;
use App\Models\Experience;
use App\Models\Project;
use App\Models\SiteContent;
use App\Models\Skill;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('CV')] class extends Component {
    public function render()
    {
        $about = SiteContent::group('about');
        $experiences = Experience::chronological()->get();

        return $this->view([
            'about' => $about,
            'contact' => SiteContent::group('contact'),
            'sections' => [
                'Experience' => $experiences->where('type', ExperienceType::Work),
                'Education' => $experiences->where('type', ExperienceType::Education),
            ],
            'skillGroups' => Skill::ordered()->get(['name', 'category'])->groupBy('category'),
            'projects' => Project::ordered()->featured()->limit(4)->get(['title', 'slug', 'role', 'year']),
        ])->layout('layouts.landing', [
            'description' => 'Work history, education and skills of ' . ($about['name'] ?? config('app.name')) . '.',
        ]);
    }
}; ?>

<div class="cv mx-auto max-w-4xl px-6 py-14 md:py-20 print:max-w-none print:p-0">
    <header class="border-b border-rule-strong pb-8">
        <div class="flex flex-wrap items-start justify-between gap-6">
            <div>
                <h1 class="display display-lg">{{ $about['name'] ?? config('app.name') }}</h1>
                <p class="label mt-4">{{ $about['role'] ?? 'Developer' }}</p>
            </div>
            <button type="button" x-data x-on:click="window.print()" class="btn-ink print:hidden">
                Save as PDF
            </button>
        </div>

        <ul class="mt-6 flex flex-wrap gap-x-6 gap-y-1 text-sm text-ink-soft">
            @if ($contact['email'] ?? null)
                <li><a href="mailto:{{ $contact['email'] }}" class="hover:text-oxblood">{{ $contact['email'] }}</a></li>
            @endif
            @if ($contact['location'] ?? null)
                <li>{{ $contact['location'] }}</li>
            @endif
            @foreach (['github_url', 'linkedin_url'] as $key)
                @if ($about[$key] ?? null)
                    <li><a href="{{ $about[$key] }}" class="hover:text-oxblood">{{ preg_replace('#^https?://(www\.)?#', '', rtrim($about[$key], '/')) }}</a></li>
                @endif
            @endforeach
        </ul>
    </header>

    @if ($about['bio'] ?? null)
        <section class="cv-section grid gap-4 border-b border-rule py-8 md:grid-cols-4">
            <h2 class="label">Profile</h2>
            <div class="paragraphs leading-relaxed text-ink-soft md:col-span-3">
                {!! \App\Support\Text::paragraphs($about['bio']) !!}
            </div>
        </section>
    @endif

    @foreach ($sections as $heading => $entries)
        @if ($entries->isNotEmpty())
            <section class="cv-section grid gap-4 border-b border-rule py-8 md:grid-cols-4">
                <h2 class="label">{{ $heading }}</h2>
                <div class="space-y-7 md:col-span-3">
                    @foreach ($entries as $entry)
                        <article class="cv-entry">
                            <div class="flex flex-wrap items-baseline justify-between gap-x-4">
                                <h3 class="display display-sm">{{ $entry->title }}</h3>
                                <span class="label tabular-nums">{{ $entry->period() }}</span>
                            </div>
                            <p class="mt-1 text-sm text-ink-muted">
                                {{ $entry->organization }}@if ($entry->location) · {{ $entry->location }}@endif
                            </p>
                            @if ($entry->summary)
                                <div class="paragraphs mt-3 leading-relaxed text-ink-soft">
                                    {!! \App\Support\Text::paragraphs($entry->summary) !!}
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    @endforeach

    @if ($projects->isNotEmpty())
        <section class="cv-section grid gap-4 border-b border-rule py-8 md:grid-cols-4">
            <h2 class="label">Selected work</h2>
            <ul class="space-y-3 md:col-span-3">
                @foreach ($projects as $project)
                    <li class="flex flex-wrap items-baseline justify-between gap-x-4">
                        <a href="{{ route('landing.projects.show', $project->slug) }}" wire:navigate
                            class="text-ink transition-colors hover:text-oxblood">{{ $project->title }}</a>
                        <span class="label">{{ collect([$project->role, $project->year])->filter()->implode(' · ') }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @if ($skillGroups->isNotEmpty())
        <section class="cv-section grid gap-4 py-8 md:grid-cols-4">
            <h2 class="label">Skills</h2>
            <dl class="space-y-2 md:col-span-3">
                @foreach ($skillGroups as $category => $skills)
                    <div class="grid gap-1 sm:grid-cols-3">
                        <dt class="text-sm text-ink-muted">{{ $category }}</dt>
                        <dd class="text-sm text-ink-soft sm:col-span-2">{{ $skills->pluck('name')->implode(', ') }}</dd>
                    </div>
                @endforeach
            </dl>
        </section>
    @endif
</div>
