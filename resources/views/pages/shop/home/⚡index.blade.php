<?php

use App\Services\Shop\CategoryMenuService;
use App\Support\CurrentDomain;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    #[Computed]
    public function categories(): array
    {
        $domain = CurrentDomain::get();

        if (! $domain) {
            return [];
        }

        return app(CategoryMenuService::class)->for($domain);
    }
};
?>

<div class="relative left-1/2 w-screen max-w-[100vw] -translate-x-1/2">
    {{-- Full-bleed hero --}}
    <section
        class="relative overflow-hidden px-4 pb-16 pt-20 text-navbar-title sm:pb-20 sm:pt-24"
        style="background:
            radial-gradient(ellipse 80% 60% at 100% 0%, rgb(0 204 205 / 0.35), transparent 55%),
            radial-gradient(ellipse 70% 50% at 0% 100%, rgb(66 58 142 / 0.9), transparent 50%),
            linear-gradient(145deg, #423A8E 0%, #1A1B25 55%, #12111A 100%);"
    >
        <div
            class="pointer-events-none absolute inset-0 opacity-30"
            style="background-image: radial-gradient(rgb(234 233 242 / 0.12) 1px, transparent 1px); background-size: 22px 22px;"
        ></div>

        <div class="relative mx-auto max-w-screen-xl">
            <p
                class="text-3xl font-bold tracking-tight text-white sm:text-4xl md:text-5xl"
                style="animation: breeze-fade-up 0.7s ease-out both;"
            >
                {{ config('app.name') }}
            </p>
            <h1
                class="mt-3 max-w-xl text-lg font-medium text-navbar-title/90 sm:text-xl"
                style="animation: breeze-fade-up 0.7s ease-out 0.12s both;"
            >
                {{ __('general.shop_hero_headline') }}
            </h1>
            <p
                class="mt-2 max-w-lg text-sm text-navbar-fg sm:text-base"
                style="animation: breeze-fade-up 0.7s ease-out 0.22s both;"
            >
                {{ __('general.shop_hero_sub') }}
            </p>

            <div
                class="mt-8 flex flex-wrap items-center gap-3"
                style="animation: breeze-fade-up 0.7s ease-out 0.32s both;"
            >
                <button
                    type="button"
                    onclick="document.getElementById('shop-search-input')?.focus()"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand/25 transition hover:bg-brand-strong focus:ring-4 focus:ring-brand/30 focus:outline-none"
                >
                    <x-lucide-search class="h-4 w-4" />
                    {{ __('general.shop_hero_cta_search') }}
                </button>
                <button
                    type="button"
                    onclick="window.dispatchEvent(new CustomEvent('shop-mobile-category-open'))"
                    class="inline-flex items-center gap-2 rounded-lg border border-navbar-title/25 bg-navbar-title/10 px-5 py-2.5 text-sm font-medium text-navbar-title backdrop-blur-sm transition hover:bg-navbar-title/20 focus:ring-4 focus:ring-brand/20 focus:outline-none md:hidden"
                >
                    <x-lucide-layout-grid class="h-4 w-4" />
                    {{ __('general.browse_categories') }}
                </button>
            </div>
        </div>
    </section>

    {{-- Category strip --}}
    @if (count($this->categories) > 0)
        <section class="relative z-10 -mt-8 px-4 pb-8">
            <div class="mx-auto max-w-screen-xl">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-semibold text-ink">{{ __('general.categories') }}</h2>
                </div>
                <div class="mt-3 -mx-1 flex gap-2 overflow-x-auto px-1 pb-2 [scrollbar-width:thin]">
                    @foreach ($this->categories as $category)
                        <a
                            href="{{ route('home', ['category' => $category['slug']]) }}"
                            wire:navigate
                            class="group flex shrink-0 items-center gap-2 rounded-full border border-nav-border bg-surface px-3 py-2 text-sm font-medium text-ink shadow-sm transition hover:border-brand hover:text-brand hover:shadow-md"
                        >
                            @if ($category['image'])
                                <img
                                    src="{{ $category['image'] }}"
                                    alt=""
                                    class="h-7 w-7 rounded-full object-cover ring-1 ring-nav-border"
                                >
                            @else
                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-accent/10 text-xs font-bold text-accent transition group-hover:bg-brand/15 group-hover:text-brand">
                                    {{ mb_substr($category['title'], 0, 1) }}
                                </span>
                            @endif
                            <span>{{ $category['title'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</div>

<style>
    @keyframes breeze-fade-up {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
