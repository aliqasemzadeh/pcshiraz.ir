<nav class="fixed top-0 start-0 z-40 w-full border-b border-nav-border bg-navbar shadow-sm">
    <div class="mx-auto flex max-w-screen-xl items-center gap-2 px-3 py-2">
        <a href="{{ route('home') }}" wire:navigate class="shrink-0">
            <span class="text-lg font-semibold whitespace-nowrap text-ink">{{ config('app.name') }}</span>
        </a>

        <div class="min-w-0 flex-1">
            @include('partials.layouts.app.search')
        </div>

        <div class="shrink-0 md:hidden">
            @include('partials.layouts.theme-compact')
        </div>
        <div class="hidden shrink-0 md:block">
            @include('partials.layouts.theme')
        </div>

        <a
            href="{{ route('home') }}"
            wire:navigate
            class="inline-flex shrink-0 items-center justify-center rounded-lg p-2.5 text-navbar-fg hover:bg-sidebar-hover hover:text-ink focus:ring-2 focus:ring-brand/30 focus:outline-none"
            title="{{ __('general.cart') }}"
        >
            <x-lucide-shopping-cart class="h-5 w-5" />
            <span class="sr-only">{{ __('general.cart') }}</span>
        </a>

        @auth
            <a
                href="{{ route('profile') }}"
                wire:navigate
                class="hidden shrink-0 items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-navbar-fg hover:bg-sidebar-hover hover:text-ink md:inline-flex"
                title="{{ __('general.profile') }}"
            >
                <x-lucide-user class="h-5 w-5" />
                <span>{{ __('general.profile') }}</span>
            </a>
        @else
            <a
                href="{{ route('login') }}"
                wire:navigate
                class="hidden shrink-0 items-center justify-center gap-2 rounded-lg bg-accent px-4 py-2 text-sm font-medium text-white hover:bg-accent-strong focus:ring-4 focus:ring-accent/30 focus:outline-none md:inline-flex"
            >
                <x-lucide-log-in class="h-5 w-5 shrink-0" />
                {{ __('general.login') }}
            </a>
        @endauth
    </div>
</nav>
