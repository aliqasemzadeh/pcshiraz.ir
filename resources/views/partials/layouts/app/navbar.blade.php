<nav class="fixed top-0 start-0 z-40 w-full bg-navbar shadow-sm dark:bg-gray-800 dark:shadow-none dark:border-b dark:border-gray-700">
    <div class="mx-auto flex max-w-screen-xl items-center gap-2 px-3 py-2">
        <a href="{{ route('home') }}" wire:navigate class="shrink-0">
            <span class="text-lg font-semibold whitespace-nowrap text-navbar-fg dark:text-white">{{ config('app.name') }}</span>
        </a>

        <div class="min-w-0 flex-1">
            @include('partials.layouts.app.search')
        </div>

        <div class="shrink-0">
            @include('partials.layouts.theme')
        </div>

        <a
            href="{{ route('home') }}"
            wire:navigate
            class="inline-flex shrink-0 items-center justify-center rounded-lg p-2.5 text-navbar-fg hover:bg-slate-100 focus:ring-2 focus:ring-brand/30 focus:outline-none dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
            title="{{ __('general.cart') }}"
        >
            <x-lucide-shopping-cart class="h-5 w-5" />
            <span class="sr-only">{{ __('general.cart') }}</span>
        </a>

        @auth
            <a
                href="{{ route('profile') }}"
                wire:navigate
                class="hidden shrink-0 items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-navbar-fg hover:bg-slate-100 md:inline-flex dark:text-gray-300 dark:hover:bg-gray-700"
                title="{{ __('general.profile') }}"
            >
                <x-lucide-user class="h-5 w-5" />
                <span>{{ __('general.profile') }}</span>
            </a>
        @else
            <a
                href="{{ route('login') }}"
                wire:navigate
                class="hidden shrink-0 items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-strong focus:ring-4 focus:ring-brand/30 focus:outline-none md:inline-flex dark:bg-brand dark:hover:bg-brand-strong dark:focus:ring-brand/40"
            >
                <x-lucide-log-in class="h-4 w-4" />
                {{ __('general.login') }}
            </a>
        @endauth
    </div>
</nav>
