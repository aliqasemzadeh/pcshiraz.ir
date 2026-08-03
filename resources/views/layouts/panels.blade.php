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

<nav class="fixed top-0 z-40 h-16 w-full border-b border-nav-border bg-navbar md:ps-[280px]">
    <div class="flex h-full items-center px-3 lg:px-5 lg:ps-3">
        <div class="flex w-full items-center justify-between">
            <div class="flex items-center justify-start rtl:justify-end">
                <button
                    data-drawer-target="panel-sidebar"
                    data-drawer-toggle="panel-sidebar"
                    data-drawer-placement="{{ __('general.direction') === 'rtl' ? 'right' : 'left' }}"
                    aria-controls="panel-sidebar"
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg p-2.5 text-sm text-navbar-fg hover:bg-slate-100 focus:ring-2 focus:ring-brand/30 focus:outline-none dark:hover:bg-slate-800 md:hidden"
                >
                    <span class="sr-only">Open sidebar</span>
                    <x-lucide-menu class="h-6 w-6" />
                </button>
                <a href="{{ route('home') }}" wire:navigate class="ms-2 flex items-center gap-2 md:me-24">
                    @if (! empty($siteLogoUrl))
                        <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? config('app.name') }}" class="h-8 w-auto max-w-[140px] object-contain">
                    @endif
                    <span class="hidden self-center text-xl font-semibold whitespace-nowrap text-ink md:inline">{{ $siteName ?? config('app.name') }}</span>
                </a>
            </div>
            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-lg px-5 py-2.5 text-sm font-medium text-navbar-fg hover:bg-slate-100 hover:text-ink dark:hover:bg-slate-800">
                        <x-lucide-log-out class="h-4 w-4" />
                        {{ __('general.logout') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<aside
    id="panel-sidebar"
    @class([
        'fixed top-0 start-0 z-50 h-screen w-[280px] border-e border-slate-800 bg-sidebar transition-transform duration-200 ease-in-out md:translate-x-0',
        'translate-x-full' => __('general.direction') === 'rtl',
        '-translate-x-full' => __('general.direction') !== 'rtl',
    ])
    aria-label="Sidebar"
>
    <div class="flex h-full flex-col overflow-y-auto bg-sidebar px-3 py-4">
        <ul class="space-y-2 font-medium">
            @if (request()->routeIs('panels.administrator.*'))
                @include('partials.layouts.navs.administrator')
            @elseif (request()->routeIs('panels.sale.*'))
                @include('partials.layouts.navs.sale')
            @elseif (request()->routeIs('panels.colleague.*'))
                @include('partials.layouts.navs.colleague')
            @elseif (request()->routeIs('panels.organization.*'))
                @include('partials.layouts.navs.organization')
            @endif
        </ul>

        <div class="mt-auto space-y-3 border-t border-slate-800 pt-4">
            <p class="px-2 text-xs font-semibold tracking-wide text-sidebar-fg uppercase">{{ __('general.switch_panel') }}</p>
            @include('partials.layouts.panels')
            <div class="flex justify-center px-2 pb-2">
                @include('partials.layouts.theme')
            </div>
        </div>
    </div>
</aside>

<main class="p-6 pt-20 md:ms-[280px]">
    {{ $slot }}
</main>

@include('partials.layouts.foot')
</body>
</html>
