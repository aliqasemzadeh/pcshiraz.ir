<nav class="fixed top-0 start-0 z-40 w-full border-b border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
    <div class="mx-auto flex max-w-screen-xl items-center gap-2 px-3 py-2">
        <a href="{{ route('home') }}" wire:navigate class="shrink-0">
            <span class="text-lg font-semibold whitespace-nowrap text-gray-900 dark:text-white">{{ config('app.name') }}</span>
        </a>

        <div class="min-w-0 flex-1">
            @include('partials.layouts.app.search')
        </div>

        <a
            href="{{ route('home') }}"
            wire:navigate
            class="inline-flex shrink-0 items-center justify-center rounded-lg p-2.5 text-gray-600 hover:bg-gray-100 focus:ring-2 focus:ring-gray-200 focus:outline-none dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
            title="{{ __('general.cart') }}"
        >
            <x-lucide-shopping-cart class="h-5 w-5" />
            <span class="sr-only">{{ __('general.cart') }}</span>
        </a>
    </div>
</nav>
