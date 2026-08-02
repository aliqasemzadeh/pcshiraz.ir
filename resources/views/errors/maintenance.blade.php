<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('app.maintenance_mode') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-canvas px-6 text-ink antialiased">
    <div class="mx-auto max-w-lg text-center">
        <div class="mb-6 inline-flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
            <x-lucide-construction class="h-8 w-8" />
        </div>
        <h1 class="mb-3 text-2xl font-semibold text-heading">{{ __('app.maintenance_mode') }}</h1>
        <p class="text-base leading-7 text-body whitespace-pre-line">{{ $message }}</p>
    </div>
</body>
</html>
