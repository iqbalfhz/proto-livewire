{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ $siteName }}</title>
        <link>{{ route('landing.blog') }}</link>
        <description>{{ Str::limit(strip_tags($siteDescription), 300) }}</description>
        <language>{{ str_replace('_', '-', app()->getLocale()) }}</language>
        <atom:link href="{{ route('feed.rss') }}" rel="self" type="application/rss+xml" />
@foreach ($posts as $post)
        <item>
            <title>{{ $post->title }}</title>
            <link>{{ route('landing.blog.show', $post->slug) }}</link>
            <guid isPermaLink="true">{{ route('landing.blog.show', $post->slug) }}</guid>
@if ($post->published_at)
            <pubDate>{{ $post->published_at->toRfc2822String() }}</pubDate>
@endif
            <description>{{ Str::limit(strip_tags($post->excerpt ?: $post->content), 500) }}</description>
        </item>
@endforeach
    </channel>
</rss>
