<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>419 — Session Expired</title>
    @vite(['resources/css/app.css'])
</head>

<body class="min-h-screen bg-white dark:bg-zinc-950 text-zinc-800 dark:text-zinc-100 flex items-center justify-center">
    <div class="text-center px-6 max-w-md">

        <p class="text-8xl font-black text-zinc-200 dark:text-zinc-800 mb-6 select-none leading-none">419</p>

        <div class="mb-6 flex justify-center">
            <div class="w-20 h-20 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                <svg class="w-10 h-10 text-orange-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
        </div>

        <h1 class="text-3xl font-bold text-zinc-900 dark:text-zinc-100 mb-2">Session Expired</h1>

        <p class="text-zinc-500 dark:text-zinc-400 mb-2 text-sm leading-relaxed">
            Your session has expired due to inactivity or the page was open too long.
        </p>

        <p class="text-zinc-400 dark:text-zinc-500 mb-8 text-xs">
            Refresh the page and try again.
        </p>

        <div class="flex items-center justify-center gap-3">
            <a href="javascript:history.back()"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg border border-zinc-200 dark:border-zinc-700 text-zinc-600 dark:text-zinc-300 text-sm font-medium hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Go Back
            </a>
            <a href="javascript:location.reload()"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-zinc-900 dark:bg-zinc-100 text-white dark:text-zinc-900 text-sm font-medium hover:bg-zinc-700 dark:hover:bg-zinc-300 transition-colors">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                Refresh Page
            </a>
        </div>

    </div>
</body>

</html>
