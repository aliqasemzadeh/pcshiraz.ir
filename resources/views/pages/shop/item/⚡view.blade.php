<?php

use App\Models\CartItem;
use App\Models\Item;
use App\Services\Sale\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new #[Layout('layouts.app')] class extends Component
{
    public Item $item;

    public function mount(Item $item): void
    {
        if (! $item->is_active) {
            abort(404);
        }

        $this->item = $item->load(['brand', 'category', 'media', 'tags', 'activeCashPrice', 'activeInstallmentPrice', 'groupVariants' => function ($q) {
            $q->active()->with(['media', 'activeCashPrice', 'activeInstallmentPrice']);
        }]);

        $item->increment('views_count');
    }

    #[Computed]
    public function cartItem(): ?CartItem
    {
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        return CartItem::query()
            ->where('item_id', $this->item->id)
            ->whereHas('cart', fn ($q) => $q->where('user_id', $user->id))
            ->first();
    }

    public function addToCart(CartService $cartService): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        try {
            $cartService->addItem(Auth::user(), $this->item, 1);
        } catch (ValidationException $e) {
            Toaster::error(collect($e->errors())->flatten()->first() ?? __('general.item_not_purchasable'));

            return;
        }

        unset($this->cartItem);
        $this->dispatch('shop.cart.updated');
        Toaster::success(__('general.added_to_cart'));
        $this->dispatch('modal-open', modal: 'shop.cart-slideover');
    }

    public function updateQuantity(mixed $quantity, CartService $cartService): void
    {
        $cartItem = $this->cartItem;

        if ($cartItem === null) {
            return;
        }

        $cartService->updateQuantity($cartItem, max(1, (int) $quantity));
        unset($this->cartItem);
        $this->dispatch('shop.cart.updated');
    }

    public function removeFromCart(CartService $cartService): void
    {
        $cartItem = $this->cartItem;

        if ($cartItem === null) {
            return;
        }

        $cartService->removeItem($cartItem);
        unset($this->cartItem);
        $this->dispatch('shop.cart.updated');
        Toaster::success(__('general.deleted'));
    }
};
?>

@php
    $cash = $item->activeCashPrice;
    $installment = $item->activeInstallmentPrice;
    $hasDiscount = $cash && (float) $cash->sale_price < (float) $cash->price;
    $cartItem = $this->cartItem;
    $canPurchase = $item->is_purchasable && ! $item->is_contact_price;
    $showStickyCart = $canPurchase;

    $galleryImages = collect();

    $mainMedia = $item->getFirstMedia('product_image');
    if ($mainMedia) {
        $galleryImages->push([
            'full' => $mainMedia->getUrl('optimized') ?: $mainMedia->getUrl(),
            'thumb' => $mainMedia->getUrl('thumb') ?: $mainMedia->getUrl(),
        ]);
    }

    foreach ($item->getMedia('gallery') as $galleryMedia) {
        $galleryImages->push([
            'full' => $galleryMedia->getUrl('optimized') ?: $galleryMedia->getUrl(),
            'thumb' => $galleryMedia->getUrl('thumb') ?: $galleryMedia->getUrl(),
        ]);
    }

    $hasSpecs = filled($item->weight) || filled($item->length) || filled($item->width) || filled($item->height);
    $lowStock = is_int($item->stock) && $item->stock > 0 && $item->stock <= 5;
    $displayPrice = $item->is_contact_price
        ? null
        : ($cash?->sale_price ?? $installment?->sale_price);
@endphp

<div @class(['space-y-8', 'pb-28 md:pb-0' => $showStickyCart])>
    <nav class="text-sm text-gray-500">
        <a href="{{ route('home') }}" wire:navigate class="hover:text-brand">{{ __('general.home') }}</a>
        <span class="mx-1">/</span>
        @if ($item->category)
            <a href="{{ route('shop.category', $item->category) }}" wire:navigate class="hover:text-brand">{{ $item->category->title }}</a>
            <span class="mx-1">/</span>
        @endif
        <span class="text-gray-800 dark:text-gray-200">{{ $item->title }}</span>
    </nav>

    <section class="lg:flex lg:justify-between lg:gap-10">
        {{-- Gallery + accordion --}}
        <div class="min-w-0 flex-1">
            <div
                class="mb-6 flex flex-col justify-center lg:mb-8 lg:flex-row lg:gap-4"
                x-data="{ active: 0 }"
            >
                @if ($galleryImages->isNotEmpty())
                    <ul class="order-2 mt-4 grid grid-cols-4 gap-3 lg:order-1 lg:mt-0 lg:block lg:space-y-3" role="tablist">
                        @foreach ($galleryImages as $index => $image)
                            <li role="presentation">
                                <button
                                    type="button"
                                    role="tab"
                                    @click="active = {{ $index }}"
                                    :aria-selected="active === {{ $index }}"
                                    :class="active === {{ $index }}
                                        ? 'border-brand ring-1 ring-brand'
                                        : 'border-gray-200 hover:border-gray-300 dark:border-gray-700 dark:hover:border-gray-600'"
                                    class="mx-auto h-20 w-20 cursor-pointer overflow-hidden rounded-lg border-2 bg-white p-1.5 transition dark:bg-gray-900 md:h-24 md:w-24"
                                >
                                    <img
                                        src="{{ $image['thumb'] }}"
                                        alt="{{ $item->title }}"
                                        class="h-full w-full object-contain"
                                    />
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <div class="order-1 flex-1 lg:order-2">
                        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                            @foreach ($galleryImages as $index => $image)
                                <div
                                    x-show="active === {{ $index }}"
                                    @if ($index > 0) x-cloak @endif
                                    class="px-4 py-6 sm:px-6"
                                >
                                    <img
                                        src="{{ $image['full'] }}"
                                        alt="{{ $item->title }}"
                                        class="mx-auto aspect-square w-full max-w-xl object-contain"
                                    />
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="flex aspect-square w-full items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-white text-gray-400 dark:border-gray-700 dark:bg-gray-900">
                        —
                    </div>
                @endif
            </div>

            <div
                class="border-t border-gray-200 dark:border-gray-700"
                x-data="{ open: @js($item->description ? 'details' : ($hasSpecs ? 'specs' : null)) }"
            >
                @if ($item->description)
                    <div>
                        <h2>
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-3 border-b border-gray-200 py-5 text-start font-medium text-gray-900 dark:border-gray-700 dark:text-white"
                                @click="open = open === 'details' ? null : 'details'"
                                :aria-expanded="open === 'details'"
                            >
                                <span>{{ __('app.product_details') }}</span>
                                <span
                                    class="inline-flex h-5 w-5 shrink-0 text-gray-500 transition-transform dark:text-gray-400"
                                    :class="open === 'details' && 'rotate-180'"
                                >
                                    <x-lucide-chevron-down class="h-5 w-5" />
                                </span>
                            </button>
                        </h2>
                        <div x-show="open === 'details'" x-cloak class="border-b border-gray-200 py-5 dark:border-gray-700">
                            <div class="prose prose-sm max-w-none text-gray-600 dark:text-gray-300">
                                {!! nl2br(e($item->description)) !!}
                            </div>
                        </div>
                    </div>
                @endif

                @if ($hasSpecs)
                    <div>
                        <h2>
                            <button
                                type="button"
                                class="flex w-full items-center justify-between gap-3 border-b border-gray-200 py-5 text-start font-medium text-gray-900 dark:border-gray-700 dark:text-white"
                                @click="open = open === 'specs' ? null : 'specs'"
                                :aria-expanded="open === 'specs'"
                            >
                                <span>{{ __('app.product_specifications') }}</span>
                                <span
                                    class="inline-flex h-5 w-5 shrink-0 text-gray-500 transition-transform dark:text-gray-400"
                                    :class="open === 'specs' && 'rotate-180'"
                                >
                                    <x-lucide-chevron-down class="h-5 w-5" />
                                </span>
                            </button>
                        </h2>
                        <div x-show="open === 'specs'" x-cloak class="border-b border-gray-200 py-5 dark:border-gray-700">
                            <dl class="grid gap-3 text-sm sm:grid-cols-2">
                                @if (filled($item->weight))
                                    <div class="flex justify-between gap-3 rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800/60">
                                        <dt class="text-gray-500 dark:text-gray-400">{{ __('general.weight') }}</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white">{{ $item->weight }}</dd>
                                    </div>
                                @endif
                                @if (filled($item->length))
                                    <div class="flex justify-between gap-3 rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800/60">
                                        <dt class="text-gray-500 dark:text-gray-400">{{ __('general.length') }}</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white">{{ $item->length }}</dd>
                                    </div>
                                @endif
                                @if (filled($item->width))
                                    <div class="flex justify-between gap-3 rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800/60">
                                        <dt class="text-gray-500 dark:text-gray-400">{{ __('general.width') }}</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white">{{ $item->width }}</dd>
                                    </div>
                                @endif
                                @if (filled($item->height))
                                    <div class="flex justify-between gap-3 rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-800/60">
                                        <dt class="text-gray-500 dark:text-gray-400">{{ __('general.height') }}</dt>
                                        <dd class="font-medium text-gray-900 dark:text-white">{{ $item->height }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Buy panel --}}
        <div class="mt-6 w-full shrink-0 lg:mt-0 lg:max-w-lg">
            <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 sm:p-6 lg:p-8 dark:border-gray-700 dark:bg-gray-800">
                @if ($item->brand)
                    <a
                        href="{{ route('shop.category.brand', [$item->category, $item->brand]) }}"
                        wire:navigate
                        class="text-sm font-medium text-brand hover:underline"
                    >
                        {{ $item->brand->title }}
                    </a>
                @endif

                <h1 class="mt-2 text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">
                    {{ $item->title }}
                </h1>

                <div class="mt-4 flex flex-wrap items-center gap-2">
                    @if ($lowStock)
                        <span class="rounded bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900 dark:text-red-300">
                            {{ __('app.last_products_left', ['count' => $item->stock]) }}
                        </span>
                    @elseif (is_int($item->stock) && $item->stock === 0)
                        <span class="rounded bg-rose-100 px-2.5 py-0.5 text-xs font-medium text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                            {{ __('app.availability_out_of_stock') }}
                        </span>
                    @endif

                    @if ($item->color_name)
                        <span class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-700 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                            <span>{{ __('general.color') }}</span>
                            @if ($item->color_code)
                                <span class="inline-block h-3.5 w-3.5 rounded-full border border-gray-300 dark:border-gray-500" style="background-color: {{ $item->color_code }}"></span>
                            @endif
                            <span>{{ $item->color_name }}</span>
                        </span>
                    @endif
                </div>

                @if ($item->is_contact_price)
                    <p class="mt-6 text-2xl font-bold text-amber-600 dark:text-amber-400">
                        {{ __('app.contact_for_price') }}
                    </p>
                @else
                    <div class="mt-6 space-y-4">
                        @if ($cash)
                            <div class="flex flex-wrap items-end justify-between gap-3">
                                <div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.cash_price') }}</div>
                                    <div class="mt-1 flex flex-wrap items-baseline gap-3">
                                        <span class="text-2xl font-extrabold text-gray-900 sm:text-3xl dark:text-white">
                                            {{ number_format((float) $cash->sale_price) }}
                                        </span>
                                        @if ($hasDiscount)
                                            <span class="text-lg text-gray-400 line-through">{{ number_format((float) $cash->price) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($installment)
                            <div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.installment_price') }}</div>
                                <div class="mt-1 text-xl font-bold text-sky-700 dark:text-sky-300 sm:text-2xl">
                                    {{ number_format((float) $installment->sale_price) }}
                                </div>
                            </div>
                        @elseif ($cash)
                            <p class="text-sm text-amber-700 dark:text-amber-300">{{ __('app.cash_only_product_hint') }}</p>
                        @endif

                        @if ($cash || $installment)
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('app.product_installment_hint') }}</p>
                        @endif
                    </div>
                @endif

                <div class="mt-6 space-y-3">
                    @if (! $item->is_purchasable)
                        <p class="rounded-lg bg-rose-50 px-3 py-2 text-sm text-rose-700 dark:bg-rose-950/40 dark:text-rose-300">
                            {{ __('app.not_purchasable') }}
                        </p>
                    @elseif ($canPurchase)
                        @if ($cartItem)
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-2">
                                    <x-ui.button
                                        type="button"
                                        size="icon"
                                        color="zinc"
                                        :loading="false"
                                        :disabled="$cartItem->quantity <= 1"
                                        wire:click="updateQuantity({{ $cartItem->quantity - 1 }})"
                                        title="{{ __('app.decrease_quantity') }}"
                                    >
                                        <x-lucide-minus class="h-4 w-4" />
                                    </x-ui.button>
                                    <span class="min-w-8 text-center text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $cartItem->quantity }}
                                    </span>
                                    <x-ui.button
                                        type="button"
                                        size="icon"
                                        color="teal"
                                        :loading="false"
                                        wire:click="updateQuantity({{ $cartItem->quantity + 1 }})"
                                        title="{{ __('app.increase_quantity') }}"
                                    >
                                        <x-lucide-plus class="h-4 w-4" />
                                    </x-ui.button>
                                </div>
                                <x-ui.button type="button" color="red" size="sm" :loading="false" wire:click="removeFromCart">
                                    <x-lucide-trash-2 class="h-4 w-4" />
                                </x-ui.button>
                                <button
                                    type="button"
                                    class="text-sm font-medium text-brand underline hover:no-underline"
                                    x-on:click="Livewire.dispatch('modal-open', { modal: 'shop.cart-slideover' })"
                                >
                                    {{ __('app.go_to_cart') }}
                                </button>
                            </div>
                        @else
                            <x-ui.button type="button" color="green" target="addToCart" wire:click="addToCart" class="w-full">
                                <x-lucide-shopping-cart class="me-2 h-5 w-5" />
                                {{ __('general.add_to_cart') }}
                            </x-ui.button>
                        @endif
                    @endif
                </div>

                @if ($item->tags->isNotEmpty())
                    <div class="mt-6 flex flex-wrap gap-2 border-t border-gray-200 pt-6 dark:border-gray-700">
                        @foreach ($item->tags as $tag)
                            <a
                                href="{{ route('shop.tag', (string) $tag->slug) }}"
                                wire:navigate
                                class="rounded-full bg-white px-3 py-1 text-xs font-medium text-gray-700 ring-1 ring-gray-200 hover:bg-brand/10 hover:text-brand dark:bg-gray-700 dark:text-gray-200 dark:ring-gray-600"
                            >
                                {{ $tag->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if ($item->groupVariants->where('id', '!=', $item->id)->isNotEmpty())
        <section class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('general.group') }}</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                @foreach ($item->groupVariants->where('id', '!=', $item->id) as $variant)
                    <x-shop.item-card :item="$variant" />
                @endforeach
            </div>
        </section>
    @endif

    @if ($showStickyCart)
        <div class="fixed inset-x-0 bottom-16 z-30 border-t border-gray-200 bg-white/95 p-3 shadow-lg backdrop-blur md:hidden dark:border-gray-700 dark:bg-gray-900/95">
            <div class="mx-auto flex max-w-lg items-center gap-3">
                <div class="min-w-0 flex-1">
                    @if ($displayPrice !== null)
                        <div class="truncate text-sm font-semibold text-gray-900 dark:text-white">
                            {{ number_format((float) $displayPrice) }}
                        </div>
                        <div class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $item->title }}</div>
                    @else
                        <div class="truncate text-sm font-medium text-gray-900 dark:text-white">{{ $item->title }}</div>
                    @endif
                </div>

                @if ($cartItem)
                    <div class="flex shrink-0 items-center gap-2">
                        <x-ui.button
                            type="button"
                            size="icon"
                            color="zinc"
                            :loading="false"
                            :disabled="$cartItem->quantity <= 1"
                            wire:click="updateQuantity({{ $cartItem->quantity - 1 }})"
                            title="{{ __('app.decrease_quantity') }}"
                        >
                            <x-lucide-minus class="h-4 w-4" />
                        </x-ui.button>
                        <span class="min-w-6 text-center text-sm font-medium text-gray-900 dark:text-white">
                            {{ $cartItem->quantity }}
                        </span>
                        <x-ui.button
                            type="button"
                            size="icon"
                            color="teal"
                            :loading="false"
                            wire:click="updateQuantity({{ $cartItem->quantity + 1 }})"
                            title="{{ __('app.increase_quantity') }}"
                        >
                            <x-lucide-plus class="h-4 w-4" />
                        </x-ui.button>
                        <x-ui.button
                            type="button"
                            color="blue"
                            size="sm"
                            :loading="false"
                            class="ms-1"
                            x-on:click="Livewire.dispatch('modal-open', { modal: 'shop.cart-slideover' })"
                        >
                            {{ __('app.go_to_cart') }}
                        </x-ui.button>
                    </div>
                @else
                    <x-ui.button type="button" color="green" size="sm" target="addToCart" wire:click="addToCart" class="shrink-0">
                        <x-lucide-shopping-cart class="me-1.5 h-4 w-4" />
                        {{ __('general.add_to_cart') }}
                    </x-ui.button>
                @endif
            </div>
        </div>
    @endif
</div>
