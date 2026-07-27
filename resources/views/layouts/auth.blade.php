<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ __('general.direction') }}">
@include('partials.layouts.head')
<body class="min-h-screen bg-gray-50 antialiased dark:bg-gray-900">
<script>
    (function () {
        const theme = localStorage.getItem('color-theme') || 'system';
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (theme === 'dark' || (theme === 'system' && prefersDark)) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>

<div class="flex min-h-screen flex-col items-center justify-center px-4 py-8">
    <div class="w-full max-w-md space-y-6">
        <div class="text-center">
            <a href="{{ route('home') }}" wire:navigate class="text-2xl font-semibold text-gray-900 dark:text-white">
                {{ config('app.name') }}
            </a>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-8">
            {{ $slot }}
        </div>

        <div class="flex justify-center">
            @include('partials.layouts.theme')
        </div>
    </div>
</div>

@include('partials.layouts.foot')
</body>
</html>
