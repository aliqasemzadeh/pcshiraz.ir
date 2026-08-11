<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ __('general.direction') }}">
@include('partials.layouts.head')
<body class="min-h-screen bg-shop-canvas text-ink antialiased">
<script>
    (function () {
        const theme = localStorage.getItem('color-theme') || 'system';
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (theme === 'dark' || (theme === 'system' && prefersDark)) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>

<script type="application/json" id="shop-category-menu-data">
    {!! json_encode($shopCategoryMenu ?? [], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}
</script>

@persist('shop-navbar')
    @include('partials.layouts.app.navbar')
@endpersist

<main class="mx-auto max-w-screen-2xl px-6 pt-20 pb-24 md:pb-8">
    {{ $slot }}
</main>

@include('partials.layouts.app.bottom-nav')

@persist('shop-mobile-categories')
    @include('partials.layouts.app.mobile-category-modal')
@endpersist

@include('partials.layouts.foot')
</body>
</html>
