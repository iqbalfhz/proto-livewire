@props(['about'])

@foreach (['github_url' => 'GitHub', 'linkedin_url' => 'LinkedIn', 'twitter_url' => 'Twitter'] as $key => $label)
    @if (filled($about[$key] ?? null))
        <li>
            <a href="{{ $about[$key] }}" target="_blank" rel="noopener noreferrer"
                class="link-sweep text-sm text-ink-soft transition-colors hover:text-oxblood">
                {{ $label }} ↗
            </a>
        </li>
    @endif
@endforeach
