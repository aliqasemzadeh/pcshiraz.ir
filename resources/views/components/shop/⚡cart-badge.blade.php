<?php

use App\Services\Sale\CartService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public string $variant = 'navbar';

    #[Computed]
    public function count(): int
    {
        return app(CartService::class)->itemsCount(Auth::user());
    }

    #[On('shop.cart.updated')]
    public function refreshCount(): void
    {
        unset($this->count);
    }

    public function openCart(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        $this->dispatch('modal-open', modal: 'shop.cart-slideover');
    }
};
?>

@if ($variant === 'bottom')
    <button
        type="button"
        wire:click="openCart"
        @class([
            'relative inline-flex flex-col items-center justify-center px-5 transition duration-200 hover:bg-brand-softer',
            'text-brand' => request()->routeIs('cart'),
            'text-slate-500 dark:text-slate-400' => ! request()->routeIs('cart'),
        ])
    >
        <span class="relative mb-1 inline-flex">
            <x-lucide-shopping-cart class="h-5 w-5" />
            @if ($this->count > 0)
                <span class="absolute -end-2 -top-2 inline-flex h-5 min-w-5 items-center justify-center rounded-full border-2 border-navbar bg-rose-600 px-1 text-[10px] font-bold leading-none text-white">
                    {{ $this->count > 99 ? '99+' : $this->count }}
                </span>
            @endif
        </span>
        <span class="text-xs">{{ __('general.cart') }}</span>
    </button>
@else
    <button
        type="button"
        wire:click="openCart"
        class="relative inline-flex shrink-0 items-center justify-center rounded-lg p-2.5 text-navbar-fg transition duration-200 hover:bg-brand-softer hover:text-brand focus:ring-2 focus:ring-brand/30 focus:outline-none"
        title="{{ __('general.cart') }}"
    >
        <x-lucide-shopping-cart class="h-5 w-5" />
        <span class="sr-only">{{ __('general.cart') }}</span>
        @if ($this->count > 0)
            <span class="absolute -end-1 -top-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full border-2 border-navbar bg-rose-600 px-1 text-[10px] font-bold leading-none text-white">
                {{ $this->count > 99 ? '99+' : $this->count }}
            </span>
        @endif
    </button>
@endif
