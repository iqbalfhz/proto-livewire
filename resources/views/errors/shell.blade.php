<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>{{ $code }} — {{ $heading }}</title>
    @fonts
    @vite(['resources/css/app.css'])
</head>

<body class="grain flex min-h-screen flex-col bg-paper text-ink antialiased">
    <header class="border-b border-rule">
        <div class="mx-auto flex max-w-4xl items-center justify-between px-6 py-4">
            <a href="/" class="display display-sm leading-none hover:text-oxblood transition-colors">
                {{ explode(' ', trim(\App\Models\SiteContent::get('about', 'name', config('app.name'))))[0] }}<span
                    class="text-oxblood">.</span>
            </a>
            <span class="label">Error {{ $code }}</span>
        </div>
    </header>

    <main class="flex flex-1 items-center">
        <div class="mx-auto w-full max-w-4xl px-6 py-20">
            <div class="flex items-center gap-4 border-b border-rule pb-4">
                <span class="index-number">{{ $code }}</span>
                <span class="h-px flex-1 bg-rule"></span>
                <span class="label">{{ $kicker }}</span>
            </div>

            <h1 class="display display-xl mt-12 mb-8">{{ $heading }}</h1>

            <p class="max-w-lg text-lg leading-relaxed text-ink-soft">{{ $body }}</p>

            <div class="mt-12 flex flex-wrap gap-4">
                <a href="/" class="btn-ink">Back to home <span aria-hidden="true">→</span></a>
                <a href="javascript:history.back()" class="btn-outline">Go back</a>
            </div>
        </div>
    </main>

    <footer class="border-t border-rule">
        <div class="mx-auto max-w-4xl px-6 py-5">
            <span class="label">© {{ date('Y') }} {{ config('app.name') }}</span>
        </div>
    </footer>
</body>

</html>
