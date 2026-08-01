@php
    $menuCategories = $shopCategoryMenu ?? [];
    $homeUrl = route('home');
@endphp

<div
    class="relative shrink-0 self-stretch"
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
            window.location.href = '/category/' + encodeURIComponent(category.slug);
        },
        goBrand(category, brand) {
            this.selectedLabel = brand.title;
            this.open = false;
            window.location.href = '/category/' + encodeURIComponent(category.slug) + '/' + encodeURIComponent(brand.slug);
        },
        initial(title) {
            return (title || '?').trim().charAt(0).toUpperCase();
        }
    }"
    @keydown.escape.window="open = false"
    @click.outside="open = false"
>
    <button
        id="shop-search-category-button"
        type="button"
        @click="open = !open"
        class="box-border inline-flex h-10 shrink-0 items-center rounded-s-lg border border-e-0 border-nav-border bg-shop-canvas px-2.5 text-sm font-medium text-ink transition duration-200 hover:bg-brand-softer focus:ring-4 focus:ring-brand/20 focus:outline-none sm:px-3"
        :aria-expanded="open.toString()"
        aria-haspopup="true"
    >
        <x-lucide-layout-grid class="me-1 h-4 w-4 shrink-0 sm:me-1.5" />
        <span class="hidden max-w-28 truncate sm:inline" x-text="selectedLabel"></span>
        <x-lucide-chevron-down class="ms-1 h-4 w-4 shrink-0 transition sm:ms-1.5" x-bind:class="open && 'rotate-180'" />
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
        class="absolute start-0 top-full z-50 mt-1 flex w-[min(100vw-1.5rem,22rem)] overflow-hidden rounded-xl border border-nav-border bg-surface p-0 shadow-xl md:w-[min(96vw,56rem)]"
        role="menu"
        aria-labelledby="shop-search-category-button"
    >
        <div class="w-2/5 max-h-72 overflow-y-auto border-e border-nav-border bg-canvas md:w-56 md:max-h-[28rem] md:shrink-0">
            <button
                type="button"
                @click="selectAll()"
                class="block w-full px-3 py-2.5 text-start text-xs font-semibold text-brand hover:bg-brand-softer md:text-sm"
            >
                {{ __('general.all_categories') }}
            </button>
            <template x-for="category in categories" :key="category.id">
                <button
                    type="button"
                    @click="selectCategory(category)"
                    @dblclick="goCategory(category)"
                    class="flex w-full items-center gap-2 px-3 py-2 text-start text-xs text-ink hover:bg-surface md:py-2.5 md:text-sm"
                    :class="activeId === category.id && 'bg-surface font-semibold text-brand shadow-sm'"
                >
                    <span class="hidden h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-surface ring-1 ring-nav-border md:inline-flex">
                        <template x-if="category.image">
                            <img :src="category.image" :alt="category.title" class="h-full w-full object-contain p-1" loading="lazy">
                        </template>
                        <template x-if="!category.image">
                            <span class="text-xs font-semibold text-brand" x-text="initial(category.title)"></span>
                        </template>
                    </span>
                    <span class="min-w-0 truncate" x-text="category.title"></span>
                </button>
            </template>
        </div>

        <div class="flex min-w-0 flex-1 flex-col max-h-72 md:max-h-[28rem]">
            <div class="flex items-center justify-between gap-2 border-b border-nav-border px-3 py-2 md:px-4 md:py-3">
                <p class="truncate text-xs font-medium text-ink md:text-sm" x-text="activeCategory?.title || @js(__('general.select_a_category'))"></p>
                <button
                    type="button"
                    class="shrink-0 text-[11px] font-medium text-brand hover:underline md:text-xs"
                    x-show="activeCategory"
                    @click="activeCategory && goCategory(activeCategory)"
                >
                    {{ __('general.view_all_in_category') }}
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-1 md:p-3">
                <template x-if="activeCategory && activeCategory.brands.length">
                    <div class="grid grid-cols-1 gap-0.5 md:grid-cols-3 md:gap-2 lg:grid-cols-4">
                        <template x-for="brand in activeCategory.brands" :key="brand.id">
                            <button
                                type="button"
                                @click="goBrand(activeCategory, brand)"
                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-start text-xs text-ink hover:bg-brand-softer hover:text-brand-strong md:flex-col md:gap-2 md:border md:border-nav-border md:px-3 md:py-3 md:text-center md:hover:border-brand md:hover:shadow-sm"
                            >
                                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-canvas ring-1 ring-nav-border md:h-14 md:w-14 md:rounded-xl">
                                    <template x-if="brand.image">
                                        <img :src="brand.image" :alt="brand.title" class="h-full w-full object-contain p-1.5" loading="lazy">
                                    </template>
                                    <template x-if="!brand.image">
                                        <span class="text-xs font-semibold text-brand md:text-base" x-text="initial(brand.title)"></span>
                                    </template>
                                </span>
                                <span class="min-w-0 truncate md:line-clamp-2 md:w-full md:whitespace-normal" x-text="brand.title"></span>
                            </button>
                        </template>
                    </div>
                </template>
                <p
                    x-show="!activeCategory || !activeCategory.brands.length"
                    class="px-3 py-6 text-center text-xs text-navbar-fg md:text-sm"
                >
                    {{ __('general.no_brands') }}
                </p>
            </div>
        </div>
    </div>
</div>
