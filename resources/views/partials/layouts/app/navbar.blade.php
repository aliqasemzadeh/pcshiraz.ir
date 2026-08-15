<nav class="fixed top-0 start-0 z-50 h-16 w-full border-b border-nav-border bg-navbar shadow-sm">
    <div class="mx-auto flex h-full max-w-screen-2xl items-center gap-2 px-3">
        <a href="{{ route('home') }}" wire:navigate class="flex shrink-0 items-center gap-2">
            @if (! empty($siteLogoUrl))
                <img src="{{ $siteLogoUrl }}" alt="{{ $siteName ?? config('app.name') }}" class="h-8 w-auto max-w-[140px] object-contain">
            @endif
            <span class="hidden text-lg font-semibold whitespace-nowrap text-ink md:inline">{{ $siteName ?? config('app.name') }}</span>
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

        <div class="hidden shrink-0 md:block">
            <livewire:shop.cart-badge variant="navbar" :key="'shop-cart-badge-navbar'" />
        </div>

        @auth
            <a
                href="{{ route('profile') }}"
                wire:navigate
                class="hidden shrink-0 items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm font-medium text-navbar-fg transition duration-200 hover:bg-brand-softer hover:text-brand md:inline-flex"
                title="{{ __('general.profile') }}"
            >
                <x-lucide-user class="h-5 w-5" />
                <span>{{ __('general.profile') }}</span>
            </a>
        @else
            <a
                href="{{ route('login') }}"
                wire:navigate
                class="hidden shrink-0 items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white transition duration-200 hover:bg-brand-strong focus:ring-4 focus:ring-brand/30 focus:outline-none md:inline-flex"
            >
                <x-lucide-log-in class="h-5 w-5 shrink-0" />
                {{ __('general.login') }}
            </a>
        @endauth
    </div>
</nav>
