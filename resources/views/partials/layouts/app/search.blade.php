<form class="w-full max-w-2xl" onsubmit="return false;">
    <div class="flex items-stretch">
        @include('partials.layouts.app.search-category-mega')

        <div class="relative w-full self-stretch">
            <input
                type="search"
                id="shop-search-input"
                class="box-border block h-10 w-full border border-slate-300 bg-white px-3 text-sm text-navbar-fg focus:border-brand focus:ring-brand dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                placeholder="{{ __('general.search_products') }}"
            >
        </div>

        <button
            type="button"
            class="box-border inline-flex h-10 shrink-0 items-center rounded-e-lg border border-brand bg-brand px-3 text-sm font-medium text-white hover:bg-brand-strong focus:ring-4 focus:ring-brand/30 focus:outline-none dark:bg-brand dark:hover:bg-brand-strong dark:focus:ring-brand/40"
        >
            <x-lucide-search class="h-4 w-4 sm:me-1.5" />
            <span class="hidden sm:inline">{{ __('general.search') }}</span>
        </button>
    </div>
</form>
