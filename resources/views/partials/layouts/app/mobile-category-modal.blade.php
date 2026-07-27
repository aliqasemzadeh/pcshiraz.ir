@php
    $menuCategories = $shopCategoryMenu ?? [];
    $homeUrl = route('home');
@endphp

<div
    x-data="{
        open: false,
        query: '',
        activeId: {{ $menuCategories[0]['id'] ?? 'null' }},
        categories: @js($menuCategories),
        get filteredCategories() {
            const q = this.query.trim().toLowerCase();
            if (!q) return this.categories;
            return this.categories.filter(c => c.title.toLowerCase().includes(q));
        },
        get activeCategory() {
            return this.categories.find(c => c.id === this.activeId) || this.filteredCategories[0] || null;
        },
        openMenu() {
            this.open = true;
            document.body.classList.add('overflow-hidden');
        },
        closeMenu() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        },
        selectCategory(category) {
            this.activeId = category.id;
        },
        goCategory(category) {
            this.closeMenu();
            window.location.href = @js($homeUrl) + '?category=' + encodeURIComponent(category.slug);
        },
        goBrand(category, brand) {
            this.closeMenu();
            window.location.href = @js($homeUrl) + '?category=' + encodeURIComponent(category.slug) + '&brand=' + encodeURIComponent(brand.slug);
        }
    }"
    @shop-mobile-category-open.window="openMenu()"
    @keydown.escape.window="open && closeMenu()"
>
    <div
        x-cloak
        x-show="open"
        class="fixed inset-0 z-50 md:hidden"
        role="dialog"
        aria-modal="true"
        aria-label="{{ __('general.browse_categories') }}"
    >
        <div
            class="absolute inset-0 bg-gray-950/50 backdrop-blur-[2px]"
            @click="closeMenu()"
            x-show="open"
            x-transition.opacity
        ></div>

        <div
            class="absolute inset-0 flex flex-col bg-white dark:bg-gray-900"
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
        >
            <div class="flex items-center gap-2 border-b border-gray-200 px-3 py-3 dark:border-gray-700">
                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800"
                    @click="closeMenu()"
                    aria-label="{{ __('general.close') }}"
                >
                    <x-lucide-x class="h-5 w-5" />
                </button>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ __('general.browse_categories') }}</p>
                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ __('general.select_a_category') }}</p>
                </div>
            </div>

            <div class="border-b border-gray-200 px-3 py-2 dark:border-gray-700">
                <div class="relative">
                    <x-lucide-search class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <input
                        type="search"
                        x-model="query"
                        placeholder="{{ __('general.search_categories') }}"
                        class="block w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pe-3 ps-9 text-sm text-gray-900 focus:border-teal-500 focus:ring-teal-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    >
                </div>
            </div>

            <div class="grid min-h-0 flex-1 grid-cols-12">
                <div class="col-span-5 overflow-y-auto border-e border-gray-100 bg-gradient-to-b from-teal-50/80 to-white dark:border-gray-700 dark:from-gray-800 dark:to-gray-900">
                    <template x-for="category in filteredCategories" :key="category.id">
                        <button
                            type="button"
                            @click="selectCategory(category)"
                            class="flex w-full items-center gap-2 border-b border-teal-100/70 px-3 py-3 text-start dark:border-gray-700"
                            :class="activeId === category.id
                                ? 'bg-white font-semibold text-teal-700 shadow-sm dark:bg-gray-900 dark:text-teal-400'
                                : 'text-gray-700 hover:bg-white/70 dark:text-gray-200 dark:hover:bg-gray-800'"
                        >
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-teal-600/10 text-teal-700 dark:bg-teal-500/10 dark:text-teal-400">
                                <x-lucide-layout-grid class="h-4 w-4" />
                            </span>
                            <span class="min-w-0 flex-1 truncate text-xs" x-text="category.title"></span>
                        </button>
                    </template>
                    <p
                        x-show="!filteredCategories.length"
                        class="px-3 py-8 text-center text-xs text-gray-500 dark:text-gray-400"
                    >
                        {{ __('general.select_a_category') }}
                    </p>
                </div>

                <div class="col-span-7 flex min-h-0 flex-col bg-white dark:bg-gray-900">
                    <div class="flex items-center justify-between gap-2 border-b border-gray-100 px-3 py-3 dark:border-gray-700">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-gray-900 dark:text-white" x-text="activeCategory?.title || @js(__('general.select_a_category'))"></p>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400" x-text="activeCategory ? (activeCategory.brands.length + ' ' + @js(__('general.brands'))) : ''"></p>
                        </div>
                        <button
                            type="button"
                            class="shrink-0 rounded-lg bg-teal-700 px-2.5 py-1.5 text-[11px] font-medium text-white hover:bg-teal-800"
                            x-show="activeCategory"
                            @click="activeCategory && goCategory(activeCategory)"
                        >
                            {{ __('general.view_all_in_category') }}
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-3">
                        <template x-if="activeCategory && activeCategory.brands.length">
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <template x-for="brand in activeCategory.brands" :key="brand.id">
                                    <button
                                        type="button"
                                        @click="goBrand(activeCategory, brand)"
                                        class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-3 text-start text-xs font-medium text-gray-800 transition hover:border-teal-300 hover:bg-teal-50 hover:text-teal-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:border-teal-600 dark:hover:bg-gray-700"
                                        x-text="brand.title"
                                    ></button>
                                </template>
                            </div>
                        </template>
                        <div
                            x-show="!activeCategory || !activeCategory.brands.length"
                            class="flex h-full flex-col items-center justify-center gap-2 px-4 text-center"
                        >
                            <x-lucide-search class="h-8 w-8 text-gray-300 dark:text-gray-600" />
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('general.no_brands') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
