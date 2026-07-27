<div class="fixed bottom-0 start-0 z-40 w-full border-t border-gray-200 bg-white md:hidden dark:border-gray-700 dark:bg-gray-800" x-data>
    <div class="mx-auto grid h-16 max-w-lg grid-cols-4 font-medium">
        <a href="{{ route('home') }}" wire:navigate class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-700 {{ request()->routeIs('home') ? 'text-teal-600 dark:text-teal-400' : 'text-gray-500 dark:text-gray-400' }}">
            <x-lucide-house class="mb-1 h-5 w-5" />
            <span class="text-xs">{{ __('general.home') }}</span>
        </a>
        <button
            type="button"
            @click="$dispatch('shop-mobile-category-open')"
            class="inline-flex flex-col items-center justify-center px-5 text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700"
        >
            <x-lucide-layout-grid class="mb-1 h-5 w-5" />
            <span class="text-xs">{{ __('general.categories') }}</span>
        </button>
        <a href="{{ route('home') }}" wire:navigate class="inline-flex flex-col items-center justify-center px-5 text-gray-500 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-700">
            <x-lucide-shopping-cart class="mb-1 h-5 w-5" />
            <span class="text-xs">{{ __('general.cart') }}</span>
        </a>
        <a href="{{ route('profile') }}" wire:navigate class="inline-flex flex-col items-center justify-center px-5 hover:bg-gray-50 dark:hover:bg-gray-700 {{ request()->routeIs('profile') ? 'text-teal-600 dark:text-teal-400' : 'text-gray-500 dark:text-gray-400' }}">
            <x-lucide-user class="mb-1 h-5 w-5" />
            <span class="text-xs">{{ __('general.profile') }}</span>
        </a>
    </div>
</div>
