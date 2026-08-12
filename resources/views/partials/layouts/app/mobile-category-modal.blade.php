@php
    $menuCategories = $shopCategoryMenu ?? [];
    $categoryUrlTemplate = route('shop.category', [
        'category' => '__CATEGORY_ID__',
        'slug' => '__CATEGORY_SLUG__',
    ]);
    $brandUrlTemplate = route('shop.category.brand', [
        'category' => '__CATEGORY_ID__',
        'slug' => '__CATEGORY_SLUG__',
        'brand' => '__BRAND_ID__',
        'brandSlug' => '__BRAND_SLUG__',
    ]);
@endphp

<div
    x-data="{
        open: false,
        query: '',
        activeId: {{ $menuCategories[0]['id'] ?? 'null' }},
        categories: (() => {
            const el = document.getElementById('shop-category-menu-data');
            return el ? JSON.parse(el.textContent) : [];
        })(),
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
            window.Alpine.navigate(
                @js($categoryUrlTemplate)
                    .replace('__CATEGORY_ID__', category.id)
                    .replace('__CATEGORY_SLUG__', encodeURIComponent(category.slug))
            );
        },
        goBrand(category, brand) {
            this.closeMenu();
            window.Alpine.navigate(
                @js($brandUrlTemplate)
                    .replace('__CATEGORY_ID__', category.id)
                    .replace('__CATEGORY_SLUG__', encodeURIComponent(category.slug))
                    .replace('__BRAND_ID__', brand.id)
                    .replace('__BRAND_SLUG__', encodeURIComponent(brand.slug))
            );
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
            class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"
            @click="closeMenu()"
            x-show="open"
            x-transition.opacity
        ></div>

        <div
            class="absolute inset-0 flex flex-col bg-surface"
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full"
        >
            <div class="flex items-center gap-2 border-b border-nav-border px-3 py-3">
                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-full text-navbar-fg hover:bg-brand-softer hover:text-ink"
                    @click="closeMenu()"
                    aria-label="{{ __('general.close') }}"
                >
                    <x-lucide-x class="h-5 w-5" />
                </button>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-ink">{{ __('general.browse_categories') }}</p>
                    <p class="truncate text-xs text-navbar-fg">{{ __('general.select_a_category') }}</p>
                </div>
            </div>

            <div class="border-b border-nav-border px-3 py-2">
                <div class="relative">
                    <x-lucide-search class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-navbar-fg" />
                    <input
                        type="search"
                        x-model="query"
                        placeholder="{{ __('general.search_categories') }}"
                        class="block w-full rounded-xl border border-nav-border bg-surface py-2.5 pe-3 ps-9 text-sm text-ink focus:border-brand focus:ring-brand"
                    >
                </div>
            </div>

            <div class="grid min-h-0 flex-1 grid-cols-12">
                <div class="col-span-5 overflow-y-auto border-e border-nav-border bg-canvas">
                    <template x-for="category in filteredCategories" :key="category.id">
                        <button
                            type="button"
                            @click="selectCategory(category)"
                            class="flex w-full items-center gap-2 border-b border-nav-border px-3 py-3 text-start"
                            :class="activeId === category.id
                                ? 'bg-surface font-semibold text-brand shadow-sm'
                                : 'text-ink hover:bg-surface/70'"
                        >
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand/10 text-brand">
                                <x-lucide-layout-grid class="h-4 w-4" />
                            </span>
                            <span class="min-w-0 flex-1 truncate text-xs" x-text="category.title"></span>
                        </button>
                    </template>
                    <p
                        x-show="!filteredCategories.length"
                        class="px-3 py-8 text-center text-xs text-navbar-fg"
                    >
                        {{ __('general.select_a_category') }}
                    </p>
                </div>

                <div class="col-span-7 flex min-h-0 flex-col bg-surface">
                    <div class="flex items-center justify-between gap-2 border-b border-nav-border px-3 py-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-ink" x-text="activeCategory?.title || @js(__('general.select_a_category'))"></p>
                            <p class="text-[11px] text-navbar-fg" x-text="activeCategory ? (activeCategory.brands.length + ' ' + @js(__('general.brands'))) : ''"></p>
                        </div>
                        <button
                            type="button"
                            class="shrink-0 rounded-lg bg-brand px-2.5 py-1.5 text-[11px] font-medium text-white hover:bg-brand-strong"
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
                                        class="rounded-xl border border-nav-border bg-canvas px-3 py-3 text-start text-xs font-medium text-ink transition hover:border-brand hover:bg-brand-softer hover:text-brand-strong"
                                        x-text="brand.title"
                                    ></button>
                                </template>
                            </div>
                        </template>
                        <div
                            x-show="!activeCategory || !activeCategory.brands.length"
                            class="flex h-full flex-col items-center justify-center gap-2 px-4 text-center"
                        >
                            <x-lucide-search class="h-8 w-8 text-navbar-fg" />
                            <p class="text-xs text-navbar-fg">{{ __('general.no_brands') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
