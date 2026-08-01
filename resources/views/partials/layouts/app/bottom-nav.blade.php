<div class="fixed bottom-0 start-0 z-40 w-full border-t border-nav-border bg-navbar md:hidden" x-data>
    <div class="mx-auto grid h-16 max-w-lg grid-cols-4 font-medium">
        <a
            href="{{ route('home') }}"
            wire:navigate
            @class([
                'inline-flex flex-col items-center justify-center px-5 transition duration-200 hover:bg-brand-softer',
                'text-brand' => request()->routeIs('home'),
                'text-slate-500 dark:text-slate-400' => ! request()->routeIs('home'),
            ])
        >
            <x-lucide-house class="mb-1 h-5 w-5" />
            <span class="text-xs">{{ __('general.home') }}</span>
        </a>
        <a
            href="{{ route('shop.price-list') }}"
            wire:navigate
            @class([
                'inline-flex flex-col items-center justify-center px-5 transition duration-200 hover:bg-brand-softer',
                'text-brand' => request()->routeIs('shop.price-list'),
                'text-slate-500 dark:text-slate-400' => ! request()->routeIs('shop.price-list'),
            ])
        >
            <x-lucide-list class="mb-1 h-5 w-5" />
            <span class="text-xs">{{ __('general.price_list') }}</span>
        </a>
        <a href="{{ route('home') }}" wire:navigate class="inline-flex flex-col items-center justify-center px-5 text-slate-500 transition duration-200 hover:bg-brand-softer dark:text-slate-400">
            <x-lucide-shopping-cart class="mb-1 h-5 w-5" />
            <span class="text-xs">{{ __('general.cart') }}</span>
        </a>
        <a
            href="{{ route('profile') }}"
            wire:navigate
            @class([
                'inline-flex flex-col items-center justify-center px-5 transition duration-200 hover:bg-brand-softer',
                'text-brand' => request()->routeIs('profile'),
                'text-slate-500 dark:text-slate-400' => ! request()->routeIs('profile'),
            ])
        >
            <x-lucide-user class="mb-1 h-5 w-5" />
            <span class="text-xs">{{ __('general.profile') }}</span>
        </a>
    </div>
</div>
