<div class="fixed bottom-0 start-0 z-40 w-full border-t border-navbar-border bg-navbar md:hidden" x-data>
    <div class="mx-auto grid h-16 max-w-lg grid-cols-4 font-medium">
        <a
            href="{{ route('home') }}"
            wire:navigate
            @class([
                'inline-flex flex-col items-center justify-center px-5 hover:bg-navbar-hover',
                'text-brand' => request()->routeIs('home'),
                'text-navbar-fg' => ! request()->routeIs('home'),
            ])
        >
            <x-lucide-house class="mb-1 h-5 w-5" />
            <span class="text-xs">{{ __('general.home') }}</span>
        </a>
        <button
            type="button"
            @click="$dispatch('shop-mobile-category-open')"
            class="inline-flex flex-col items-center justify-center px-5 text-navbar-fg hover:bg-navbar-hover hover:text-navbar-title"
        >
            <x-lucide-layout-grid class="mb-1 h-5 w-5" />
            <span class="text-xs">{{ __('general.categories') }}</span>
        </button>
        <a href="{{ route('home') }}" wire:navigate class="inline-flex flex-col items-center justify-center px-5 text-navbar-fg hover:bg-navbar-hover hover:text-navbar-title">
            <x-lucide-shopping-cart class="mb-1 h-5 w-5" />
            <span class="text-xs">{{ __('general.cart') }}</span>
        </a>
        <a
            href="{{ route('profile') }}"
            wire:navigate
            @class([
                'inline-flex flex-col items-center justify-center px-5 hover:bg-navbar-hover',
                'text-brand' => request()->routeIs('profile'),
                'text-navbar-fg' => ! request()->routeIs('profile'),
            ])
        >
            <x-lucide-user class="mb-1 h-5 w-5" />
            <span class="text-xs">{{ __('general.profile') }}</span>
        </a>
    </div>
</div>
