@php($name = $about['name'] ?? config('app.name'))
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>{{ $name }} - CV</title>
    <style>
        @page { margin: 18mm 18mm 16mm; }

        @font-face {
            font-family: 'Newsreader';
            src: url('{{ resource_path('fonts/Newsreader-Variable.ttf') }}') format('truetype');
        }

        @font-face {
            font-family: 'JetBrains Mono';
            src: url('{{ resource_path('fonts/JetBrainsMono-Variable.ttf') }}') format('truetype');
        }

        body { font-family: Helvetica, sans-serif; font-size: 9.5pt; line-height: 1.5; color: #46403a; }
        a { color: inherit; text-decoration: none; }
        p { margin: 0 0 6pt; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 0; }

        .name { font-family: 'Newsreader', serif; font-weight: normal; font-size: 30pt; line-height: 1; color: #191612; margin: 0; }
        .label { font-family: 'JetBrains Mono', monospace; font-size: 7pt; letter-spacing: 1.2pt; text-transform: uppercase; color: #6e6559; }
        .role { margin-top: 7pt; color: #7b2d26; }
        .contact { margin-top: 10pt; font-size: 8.5pt; }
        .masthead { border-bottom: 1pt solid #191612; }
        .masthead td { padding-bottom: 12pt; }
        .masthead-text { vertical-align: bottom; }
        .photo-cell { width: 26mm; text-align: right; }
        .photo { width: 24mm; height: 30mm; }

        .section { border-bottom: 0.5pt solid #d6d0c4; }
        .section > tbody > tr > td { padding: 12pt 0; }
        .section-heading { width: 27%; padding-top: 14pt !important; }
        .last { border-bottom: 0; }

        .entry { margin-bottom: 11pt; page-break-inside: avoid; }
        .entry:last-child { margin-bottom: 0; }
        .entry-title { font-family: 'Newsreader', serif; font-size: 13pt; line-height: 1.2; color: #191612; }
        .period { text-align: right; white-space: nowrap; padding-top: 3pt; }
        .meta { font-size: 8.5pt; color: #6e6559; margin: 1pt 0 4pt; }
        .row td { padding-bottom: 4pt; }
        .row .period { padding-top: 1.5pt; }
        .muted { color: #6e6559; }
    </style>
</head>

<body>
    <table class="masthead">
        <tr>
            <td class="masthead-text">
                <h1 class="name">{{ $name }}</h1>
                <div class="label role">{{ $about['role'] ?? 'Developer' }}</div>
                @php($links = array_filter(['mailto:'.($contact['email'] ?? '') => $contact['email'] ?? null]) + \App\Support\CurriculumVitae::links($about))
                <div class="contact">
                    @foreach ($links as $url => $text)
                        <a href="{{ $url }}">{{ $text }}</a>@unless ($loop->last) &nbsp;·&nbsp; @endunless
                    @endforeach
                    @if ($contact['location'] ?? null)
                        @if ($links) &nbsp;·&nbsp; @endif{{ $contact['location'] }}
                    @endif
                </div>
            </td>
            @if ($photo ?? null)
                <td class="photo-cell"><img src="{{ $photo }}" class="photo" alt="{{ $name }}"></td>
            @endif
        </tr>
    </table>

    @if ($about['bio'] ?? null)
        <table class="section">
            <tr>
                <td class="section-heading label">Profile</td>
                <td>{!! \App\Support\Text::paragraphs($about['bio']) !!}</td>
            </tr>
        </table>
    @endif

    @foreach ($sections as $heading => $entries)
        @if ($entries->isNotEmpty())
            <table class="section">
                <tr>
                    <td class="section-heading label">{{ $heading }}</td>
                    <td>
                        @foreach ($entries as $entry)
                            <div class="entry">
                                <table>
                                    <tr>
                                        <td class="entry-title">{{ $entry->title }}</td>
                                        <td class="label period">{{ $entry->period() }}</td>
                                    </tr>
                                </table>
                                <div class="meta">
                                    {{ $entry->organization }}@if ($entry->location) · {{ $entry->location }}@endif
                                </div>
                                {!! \App\Support\Text::paragraphs($entry->summary) !!}
                            </div>
                        @endforeach
                    </td>
                </tr>
            </table>
        @endif
    @endforeach

    @if ($projects->isNotEmpty())
        <table class="section">
            <tr>
                <td class="section-heading label">Selected work</td>
                <td>
                    <table>
                        @foreach ($projects as $project)
                            <tr class="row">
                                <td>
                                    <a href="{{ route('landing.projects.show', $project->slug) }}">{{ $project->title }}</a>
                                </td>
                                <td class="label period">{{ collect([$project->role, $project->year])->filter()->implode(' · ') }}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
    @endif

    @if ($skillGroups->isNotEmpty())
        <table class="section last">
            <tr>
                <td class="section-heading label">Skills</td>
                <td>
                    <table>
                        @foreach ($skillGroups as $category => $skills)
                            <tr class="row">
                                <td class="muted" style="width: 30%">{{ $category }}</td>
                                <td>{{ $skills->pluck('name')->implode(', ') }}</td>
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        </table>
    @endif
</body>

</html>
