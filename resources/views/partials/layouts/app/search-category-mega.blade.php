@php
    $menuCategories = $shopCategoryMenu ?? [];
    $homeUrl = route('home');
@endphp

<div
    class="relative shrink-0"
    x-data="{
        open: false,
        activeId: {{ $menuCategories[0]['id'] ?? 'null' }},
        selectedLabel: @js(__('general.all_categories')),
        categories: @js($menuCategories),
        get activeCategory() {
            return this.categories.find(c => c.id === this.activeId) || null;
        },
        selectAll() {
            this.selectedLabel = @js(__('general.all_categories'));
            this.open = false;
            window.location.href = @js($homeUrl);
        },
        selectCategory(category) {
            this.activeId = category.id;
        },
        goCategory(category) {
            this.selectedLabel = category.title;
            this.open = false;
            window.location.href = @js($homeUrl) + '?category=' + encodeURIComponent(category.slug);
        },
        goBrand(category, brand) {
            this.selectedLabel = brand.title;
            this.open = false;
            window.location.href = @js($homeUrl) + '?category=' + encodeURIComponent(category.slug) + '&brand=' + encodeURIComponent(brand.slug);
        }
    }"
    @keydown.escape.window="open = false"
    @click.outside="open = false"
>
    <button
        id="shop-search-category-button"
        type="button"
        @click="open = !open"
        class="inline-flex shrink-0 items-center rounded-s-lg border border-e-0 border-gray-300 bg-gray-50 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 dark:focus:ring-gray-700"
        :aria-expanded="open.toString()"
        aria-haspopup="true"
    >
        <x-lucide-layout-grid class="me-1.5 h-4 w-4" />
        <span class="hidden max-w-28 truncate sm:inline" x-text="selectedLabel"></span>
        <x-lucide-chevron-down class="ms-1.5 h-4 w-4 transition" :class="open && 'rotate-180'" />
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute start-0 top-full z-50 mt-1 flex w-[min(100vw-1.5rem,22rem)] overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-800"
        role="menu"
        aria-labelledby="shop-search-category-button"
    >
        <div class="w-2/5 max-h-72 overflow-y-auto border-e border-gray-100 bg-gray-50 dark:border-gray-700 dark:bg-gray-900/40">
            <button
                type="button"
                @click="selectAll()"
                class="block w-full px-3 py-2.5 text-start text-xs font-semibold text-teal-700 hover:bg-teal-50 dark:text-teal-400 dark:hover:bg-gray-700"
            >
                {{ __('general.all_categories') }}
            </button>
            <template x-for="category in categories" :key="category.id">
                <button
                    type="button"
                    @click="selectCategory(category)"
                    @dblclick="goCategory(category)"
                    class="block w-full px-3 py-2 text-start text-xs text-gray-700 hover:bg-white dark:text-gray-200 dark:hover:bg-gray-700"
                    :class="activeId === category.id && 'bg-white font-semibold text-teal-700 shadow-sm dark:bg-gray-800 dark:text-teal-400'"
                    x-text="category.title"
                ></button>
            </template>
        </div>

        <div class="flex w-3/5 max-h-72 flex-col">
            <div class="flex items-center justify-between gap-2 border-b border-gray-100 px-3 py-2 dark:border-gray-700">
                <p class="truncate text-xs font-medium text-gray-900 dark:text-white" x-text="activeCategory?.title || @js(__('general.select_a_category'))"></p>
                <button
                    type="button"
                    class="shrink-0 text-[11px] font-medium text-teal-700 hover:underline dark:text-teal-400"
                    x-show="activeCategory"
                    @click="activeCategory && goCategory(activeCategory)"
                >
                    {{ __('general.view_all_in_category') }}
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-1">
                <template x-if="activeCategory && activeCategory.brands.length">
                    <div>
                        <template x-for="brand in activeCategory.brands" :key="brand.id">
                            <button
                                type="button"
                                @click="goBrand(activeCategory, brand)"
                                class="block w-full rounded-lg px-3 py-2 text-start text-xs text-gray-700 hover:bg-teal-50 hover:text-teal-800 dark:text-gray-200 dark:hover:bg-gray-700 dark:hover:text-white"
                                x-text="brand.title"
                            ></button>
                        </template>
                    </div>
                </template>
                <p
                    x-show="!activeCategory || !activeCategory.brands.length"
                    class="px-3 py-6 text-center text-xs text-gray-500 dark:text-gray-400"
                >
                    {{ __('general.no_brands') }}
                </p>
            </div>
        </div>
    </div>
</div>
