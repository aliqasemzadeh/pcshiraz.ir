<form class="w-full max-w-2xl" onsubmit="return false;">
    <div class="flex">
        <button
            id="shop-search-category-button"
            data-dropdown-toggle="shop-search-category-dropdown"
            data-dropdown-placement="bottom-start"
            type="button"
            class="inline-flex shrink-0 items-center rounded-s-lg border border-e-0 border-gray-300 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 dark:focus:ring-gray-700"
        >
            <x-lucide-layout-grid class="me-1.5 h-4 w-4" />
            <span class="hidden sm:inline">{{ __('general.all_categories') }}</span>
            <x-lucide-chevron-down class="ms-1.5 h-4 w-4" />
        </button>

        <div id="shop-search-category-dropdown" class="z-50 hidden w-44 divide-y divide-gray-100 rounded-lg border border-gray-200 bg-white shadow dark:border-gray-600 dark:bg-gray-700">
            <ul class="py-1 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="shop-search-category-button">
                <li>
                    <button type="button" class="block w-full px-4 py-2 text-start hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">{{ __('general.all_categories') }}</button>
                </li>
                <li>
                    <button type="button" class="block w-full px-4 py-2 text-start hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">{{ __('general.items') }}</button>
                </li>
                <li>
                    <button type="button" class="block w-full px-4 py-2 text-start hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">{{ __('general.brands') }}</button>
                </li>
                <li>
                    <button type="button" class="block w-full px-4 py-2 text-start hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">{{ __('general.categories') }}</button>
                </li>
            </ul>
        </div>

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
