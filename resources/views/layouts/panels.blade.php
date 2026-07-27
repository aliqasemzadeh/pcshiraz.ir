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

<nav class="fixed top-0 z-50 w-full border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
    <div class="px-3 py-3 lg:px-5 lg:ps-3">
        <div class="flex items-center justify-between">
            <div class="flex items-center justify-start rtl:justify-end">
                <button
                    data-drawer-target="panel-sidebar"
                    data-drawer-toggle="panel-sidebar"
                    aria-controls="panel-sidebar"
                    type="button"
                    class="inline-flex items-center rounded-lg p-2 text-sm text-gray-500 hover:bg-gray-100 focus:ring-2 focus:ring-gray-200 focus:outline-none md:hidden dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                >
                    <span class="sr-only">Open sidebar</span>
                    <x-lucide-menu class="h-6 w-6" />
                </button>
                <a href="{{ route('home') }}" wire:navigate class="ms-2 flex md:me-24">
                    <span class="self-center text-xl font-semibold whitespace-nowrap dark:text-white">{{ config('app.name') }}</span>
                </a>
            </div>
            <div class="flex items-center gap-3">
                @include('partials.layouts.theme')
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
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
    class="fixed top-0 start-0 z-40 h-screen w-64 -translate-x-full border-e border-gray-200 bg-white pt-14 transition-transform md:translate-x-0 rtl:translate-x-full md:rtl:translate-x-0 dark:border-gray-700 dark:bg-gray-800"
    aria-label="Sidebar"
>
    <div class="flex h-full flex-col overflow-y-auto bg-white px-3 py-4 dark:bg-gray-800">
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

        <div class="mt-auto space-y-3 border-t border-gray-200 pt-4 dark:border-gray-700">
            <p class="px-2 text-xs font-semibold tracking-wide text-gray-400 uppercase">{{ __('general.switch_panel') }}</p>
            @include('partials.layouts.panels')
        </div>
    </div>
</aside>

<main class="p-4 md:ms-64 pt-20">
    {{ $slot }}
</main>

@include('partials.layouts.foot')
</body>
</html>
