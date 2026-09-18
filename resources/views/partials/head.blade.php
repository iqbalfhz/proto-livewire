<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

@php
    $siteName = config('app.name', 'Laravel');
    $seoTitle = filled($title ?? null) ? $title . ' - ' . $siteName : $siteName;
    $seoDescription = \Illuminate\Support\Str::limit(
        trim(preg_replace('/\s+/', ' ', strip_tags((string) ($description ?? '')))),
        160,
    );
    $seoUrl = url()->current();
    $seoImage = $ogImage ?? null;
@endphp

<title>{{ $seoTitle }}</title>

@if ($noindex ?? false)
    <meta name="robots" content="noindex, nofollow" />
@else
    <link rel="canonical" href="{{ $seoUrl }}" />
@endif

@if ($seoDescription)
    <meta name="description" content="{{ $seoDescription }}" />
@endif

{{-- Open Graph --}}
<meta property="og:site_name" content="{{ $siteName }}" />
<meta property="og:type" content="{{ $ogType ?? 'website' }}" />
<meta property="og:url" content="{{ $seoUrl }}" />
<meta property="og:title" content="{{ $seoTitle }}" />
@if ($seoDescription)
    <meta property="og:description" content="{{ $seoDescription }}" />
@endif
@if ($seoImage)
    <meta property="og:image" content="{{ $seoImage }}" />
@endif

{{-- Twitter / X --}}
<meta name="twitter:card" content="{{ $seoImage ? 'summary_large_image' : 'summary' }}" />
<meta name="twitter:title" content="{{ $seoTitle }}" />
@if ($seoDescription)
    <meta name="twitter:description" content="{{ $seoDescription }}" />
@endif
@if ($seoImage)
    <meta name="twitter:image" content="{{ $seoImage }}" />
@endif

@isset($jsonLd)
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endisset

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

@unless ($noindex ?? false)
    <link rel="alternate" type="application/rss+xml" title="{{ $siteName }} — Blog"
        href="{{ route('feed.rss') }}" />
@endunless

@fonts

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
