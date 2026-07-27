<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ __('general.direction') }}">
@include('partials.layouts.head')
<body class="min-h-screen bg-navbar antialiased dark:bg-gray-900">
<script>
    (function () {
        const theme = localStorage.getItem('color-theme') || 'system';
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (theme === 'dark' || (theme === 'system' && prefersDark)) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>

@include('partials.layouts.app.navbar')

<main class="mx-auto max-w-screen-xl px-4 pt-16 pb-24 md:pb-8">
    {{ $slot }}
</main>

@include('partials.layouts.app.bottom-nav')
@include('partials.layouts.app.mobile-category-modal')
@include('partials.layouts.foot')
</body>
</html>
