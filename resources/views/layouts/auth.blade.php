<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ __('general.direction') }}">
@include('partials.layouts.head')
<body class="min-h-screen bg-canvas text-ink antialiased">
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
            <a href="{{ route('home') }}" wire:navigate class="inline-flex items-center justify-center gap-2 text-2xl font-semibold text-ink">
                @if (! empty($siteLogoUrl))
                    <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? config('app.name') }}" class="h-10 w-auto max-w-[160px] object-contain">
                @endif
                <span class="hidden md:inline">{{ $siteName ?? config('app.name') }}</span>
            </a>
        </div>

        <div class="rounded-2xl border border-nav-border bg-surface p-6 shadow-sm sm:p-8">
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
