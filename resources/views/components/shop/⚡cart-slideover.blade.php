<?php

use App\Models\Cart;
use App\Services\Sale\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    #[Computed]
    public function cart(): ?Cart
    {
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        return Cart::query()
            ->where('user_id', $user->id)
            ->with(['items.item.media'])
            ->first();
    }

    #[Computed]
    public function subtotal(): string
    {
        if ($this->cart === null || $this->cart->items->isEmpty()) {
            return '0.0000';
        }

        return app(CartService::class)->subtotal($this->cart);
    }

    #[Computed]
    public function itemsCount(): int
    {
        if ($this->cart === null) {
            return 0;
        }

        return (int) $this->cart->items->sum('quantity');
    }

    #[On('shop.cart.updated')]
    public function refreshCart(): void
    {
        unset($this->cart, $this->subtotal, $this->itemsCount);
    }

    public function updateQuantity(int $cartItemId, mixed $quantity): void
    {
        if ($this->cart === null) {
            return;
        }

        $cartItem = $this->cart->items->firstWhere('id', $cartItemId);

        if ($cartItem === null) {
            return;
        }

        app(CartService::class)->updateQuantity($cartItem, max(1, (int) $quantity));
        unset($this->cart, $this->subtotal, $this->itemsCount);
        $this->dispatch('shop.cart.updated');
    }

    public function removeItem(int $cartItemId): void
    {
        if ($this->cart === null) {
            return;
        }

        $cartItem = $this->cart->items->firstWhere('id', $cartItemId);

        if ($cartItem === null) {
            return;
        }

        app(CartService::class)->removeItem($cartItem);
        unset($this->cart, $this->subtotal, $this->itemsCount);
        $this->dispatch('shop.cart.updated');
        Toaster::success(__('general.deleted'));
    }
};
?>

<x-livewire-modal::stack>
    <x-livewire-modal::slideover
        position="{{ __('general.direction') === 'rtl' ? 'end' : 'start' }}"
        class="flex h-full w-full max-w-md flex-col overflow-hidden bg-white p-0 dark:bg-gray-900"
    >
        <div class="flex h-full flex-col">
            <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-5 py-4 pe-14 dark:border-gray-700">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('general.cart') }}
                    </h2>
                    @if ($this->itemsCount > 0)
                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('app.cart_items_count', ['count' => $this->itemsCount]) }}
                        </p>
                    @endif
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-5 py-4">
                @if ($this->cart === null || $this->cart->items->isEmpty())
                    <div class="flex h-full min-h-48 flex-col items-center justify-center gap-3 text-center">
                        <span class="inline-flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800">
                            <x-lucide-shopping-cart class="h-7 w-7" />
                        </span>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ __('general.cart_is_empty') }}
                        </p>
                        <x-ui.button
                            type="button"
                            color="zinc"
                            :loading="false"
                            class="w-full max-w-xs"
                            x-on:click="$dispatch('modal-close')"
                        >
                            {{ __('app.continue_shopping') }}
                        </x-ui.button>
                    </div>
                @else
                    <ul class="space-y-4">
                        @foreach ($this->cart->items as $cartItem)
                            @php
                                $item = $cartItem->item;
                                $media = $item?->getFirstMedia('product_image');
                                $imageUrl = $media ? ($media->getUrl('thumb') ?: $media->getUrl()) : null;
                                $lineTotal = (float) $cartItem->unit_price * $cartItem->quantity;
                            @endphp
                            <li class="flex gap-3 border-b border-gray-100 pb-4 last:border-0 last:pb-0 dark:border-gray-800">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                                    @if ($imageUrl)
                                        <img
                                            src="{{ $imageUrl }}"
                                            alt="{{ $item?->title }}"
                                            class="h-full w-full object-contain p-1"
                                            loading="lazy"
                                        />
                                    @else
                                        <div class="flex h-full items-center justify-center text-xs text-gray-400">—</div>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1 space-y-2">
                                    <div class="flex items-start justify-between gap-2">
                                        @if ($item)
                                            <a
                                                href="{{ route('shop.item', $item) }}"
                                                wire:navigate
                                                x-on:click="$dispatch('modal-close')"
                                                class="line-clamp-2 text-sm font-medium text-gray-900 hover:text-brand dark:text-white"
                                            >
                                                {{ $item->title }}
                                            </a>
                                        @else
                                            <span class="text-sm text-gray-500">—</span>
                                        @endif

                                        <x-ui.button
                                            type="button"
                                            size="icon"
                                            color="red"
                                            :loading="false"
                                            wire:click="removeItem({{ $cartItem->id }})"
                                            title="{{ __('general.delete') }}"
                                            class="shrink-0"
                                        >
                                            <x-lucide-trash-2 class="h-4 w-4" />
                                        </x-ui.button>
                                    </div>

                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <div class="flex items-center gap-1.5">
                                            <x-ui.button
                                                type="button"
                                                size="icon"
                                                color="zinc"
                                                :loading="false"
                                                :disabled="$cartItem->quantity <= 1"
                                                wire:click="updateQuantity({{ $cartItem->id }}, {{ $cartItem->quantity - 1 }})"
                                                title="{{ __('app.decrease_quantity') }}"
                                            >
                                                <x-lucide-minus class="h-3.5 w-3.5" />
                                            </x-ui.button>
                                            <span class="min-w-7 text-center text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $cartItem->quantity }}
                                            </span>
                                            <x-ui.button
                                                type="button"
                                                size="icon"
                                                color="teal"
                                                :loading="false"
                                                wire:click="updateQuantity({{ $cartItem->id }}, {{ $cartItem->quantity + 1 }})"
                                                title="{{ __('app.increase_quantity') }}"
                                            >
                                                <x-lucide-plus class="h-3.5 w-3.5" />
                                            </x-ui.button>
                                        </div>

                                        <div class="text-end">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ number_format($lineTotal) }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ number_format((float) $cartItem->unit_price) }} × {{ $cartItem->quantity }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            @if ($this->cart !== null && $this->cart->items->isNotEmpty())
                <div class="border-t border-gray-200 bg-gray-50 px-5 py-4 dark:border-gray-700 dark:bg-gray-800/80">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <span class="text-sm text-gray-600 dark:text-gray-300">{{ __('general.subtotal') }}</span>
                        <span class="text-base font-bold text-gray-900 dark:text-white">
                            {{ number_format((float) $this->subtotal) }}
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('app.rial') }}</span>
                        </span>
                    </div>

                    <div class="space-y-2">
                        <x-ui.button
                            :href="route('cart')"
                            color="green"
                            :loading="false"
                            class="w-full"
                            wire:navigate
                            x-on:click="$dispatch('modal-close')"
                        >
                            {{ __('app.finalize_order') }}
                        </x-ui.button>
                        <x-ui.button
                            type="button"
                            color="zinc"
                            :loading="false"
                            class="w-full"
                            x-on:click="$dispatch('modal-close')"
                        >
                            {{ __('app.continue_shopping') }}
                        </x-ui.button>
                    </div>
                </div>
            @endif
        </div>
    </x-livewire-modal::slideover>
</x-livewire-modal::stack>
