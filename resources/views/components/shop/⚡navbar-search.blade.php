<?php

use App\Services\Shop\ProductSearchService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $q = '';

    public bool $open = false;

    public function updatedQ(): void
    {
        $this->open = true;
    }

    public function openPanel(): void
    {
        $this->open = true;
    }

    public function closePanel(): void
    {
        $this->open = false;
    }

    public function submit(): void
    {
        $this->open = true;
    }

    #[Computed]
    public function results(): Collection
    {
        return app(ProductSearchService::class)->search($this->q);
    }
};
?>

<div
    class="relative flex min-w-0 flex-1 items-stretch self-stretch"
    x-data="{
        open: $wire.entangle('open').live,
        isDesktop() {
            return window.matchMedia('(min-width: 768px)').matches;
        },
        syncBodyScroll() {
            if (this.open && ! this.isDesktop()) {
                document.body.classList.add('overflow-hidden');
            } else {
                document.body.classList.remove('overflow-hidden');
            }
        },
        openPanel() {
            this.open = true;
            this.$nextTick(() => this.syncBodyScroll());
        },
        closePanel() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        },
        submitSearch() {
            this.openPanel();
            $wire.submit();
        }
    }"
    x-effect="syncBodyScroll()"
    @keydown.escape.window="open && closePanel()"
    @click.outside="open && isDesktop() && closePanel()"
>
    <div class="relative min-w-0 flex-1 self-stretch">
        <input
            type="search"
            id="shop-search-input"
            wire:model.live.debounce.300ms="q"
            @focus="openPanel()"
            @keydown.enter.prevent="submitSearch()"
            class="box-border block h-10 w-full border border-slate-300 bg-surface px-3 text-sm text-ink placeholder:text-slate-500 focus:border-brand focus:ring-brand dark:border-slate-700 dark:placeholder:text-slate-500"
            placeholder="{{ __('general.search_products') }}"
            autocomplete="off"
            aria-autocomplete="list"
            aria-controls="shop-search-results"
            :aria-expanded="open.toString()"
        >
    </div>

    <button
        type="button"
        wire:click="submit"
        @click="submitSearch()"
        class="box-border inline-flex h-10 shrink-0 items-center rounded-e-lg border border-brand bg-brand px-3 text-sm font-medium text-white hover:bg-brand-strong focus:ring-4 focus:ring-brand/30 focus:outline-none"
    >
        <x-lucide-search class="h-4 w-4 sm:me-1.5" />
        <span class="hidden sm:inline">{{ __('general.search') }}</span>
    </button>

    {{-- Desktop mega panel --}}
    <div
        x-cloak
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        id="shop-search-results"
        role="listbox"
        aria-label="{{ __('general.search_results') }}"
        class="absolute end-0 start-0 top-full z-50 mt-1 max-h-[min(28rem,70vh)] w-full overflow-hidden rounded-xl border border-nav-border bg-surface shadow-xl max-md:hidden md:w-[min(96vw,40rem)] md:min-w-full"
    >
        <div class="max-h-[min(28rem,70vh)] overflow-y-auto">
            @include('partials.shop.search-results-body')
        </div>
    </div>

    {{-- Mobile full-screen sheet --}}
    <div
        x-cloak
        x-show="open"
        class="fixed inset-0 z-50 md:hidden"
        role="dialog"
        aria-modal="true"
        aria-label="{{ __('general.search_results') }}"
    >
        <div
            class="absolute inset-0 bg-black/50 backdrop-blur-[2px]"
            @click="closePanel()"
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
                    class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-navbar-fg hover:bg-brand-softer hover:text-ink"
                    @click="closePanel()"
                    aria-label="{{ __('general.close') }}"
                >
                    <x-lucide-x class="h-5 w-5" />
                </button>
                <div class="relative min-w-0 flex-1">
                    <x-lucide-search class="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-navbar-fg" />
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="q"
                        x-ref="mobileSearch"
                        x-init="$watch('open', value => { if (value) $nextTick(() => $refs.mobileSearch?.focus()) })"
                        placeholder="{{ __('general.search_products') }}"
                        class="block w-full rounded-xl border border-nav-border bg-surface py-2.5 pe-3 ps-9 text-sm text-ink focus:border-brand focus:ring-brand"
                        autocomplete="off"
                    >
                </div>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto">
                @include('partials.shop.search-results-body', ['mobile' => true])
            </div>
        </div>
    </div>
</div>
