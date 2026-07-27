<form class="w-full max-w-2xl" onsubmit="return false;">
    <div class="flex">
        @include('partials.layouts.app.search-category-mega')

        <div class="relative w-full">
            <input
                type="search"
                id="shop-search-input"
                class="block w-full border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-900 focus:border-teal-500 focus:ring-teal-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                placeholder="{{ __('general.search_products') }}"
            >
        </div>

        <button
            type="button"
            class="inline-flex shrink-0 items-center rounded-e-lg border border-teal-700 bg-teal-700 px-3 py-2 text-sm font-medium text-white hover:bg-teal-800 focus:ring-4 focus:ring-teal-300 focus:outline-none dark:bg-teal-600 dark:hover:bg-teal-700 dark:focus:ring-teal-800"
        >
            <x-lucide-search class="h-4 w-4 sm:me-1.5" />
            <span class="hidden sm:inline">{{ __('general.search') }}</span>
        </button>
    </div>
</form>
