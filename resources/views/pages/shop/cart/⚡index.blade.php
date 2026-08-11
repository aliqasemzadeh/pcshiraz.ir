<?php

use App\Enums\PriceTypeEnum;
use App\Services\Sale\CartService;
use App\Services\Sale\CheckoutService;
use App\Services\Sale\InstallmentPlanMatcher;
use App\Support\PersianNumberToWords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Masmerise\Toaster\Toaster;

new #[Layout('layouts.app')] class extends Component
{
    public string $organization_code = '';

    public ?int $installment_plan_id = null;

    public bool $codeValidated = false;

    public ?int $organizationId = null;

    #[Computed]
    public function cart()
    {
        $user = Auth::user();

        if ($user === null) {
            return null;
        }

        return app(CartService::class)->getOrCreateCart($user)->load(['items.item.media', 'items.itemPrice']);
    }

    #[Computed]
    public function breakdown(): array
    {
        if ($this->cart === null) {
            return [
                'subtotal' => '0.0000',
                'cash_only_subtotal' => '0.0000',
                'installment_subtotal' => '0.0000',
                'cash_only_count' => 0,
            ];
        }

        return app(CartService::class)->breakdown($this->cart);
    }

    #[Computed]
    public function subtotal(): string
    {
        return $this->breakdown['subtotal'];
    }

    #[Computed]
    public function eligiblePlans()
    {
        if (! $this->codeValidated || $this->organizationId === null || ! $this->isInstallmentSale()) {
            return collect();
        }

        if (bccomp($this->breakdown['installment_subtotal'], '0', 4) <= 0) {
            return collect();
        }

        $organization = \App\Models\Organization::query()->find($this->organizationId);

        if ($organization === null) {
            return collect();
        }

        return app(InstallmentPlanMatcher::class)->eligiblePlans(
            $organization,
            $this->breakdown['installment_subtotal'],
            $this->breakdown['cash_only_subtotal'],
        );
    }

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: true);
        }
    }

    public function setSaleType(string $saleType): void
    {
        if ($this->cart === null) {
            return;
        }

        try {
            $type = PriceTypeEnum::from($saleType);
            $result = app(CartService::class)->setSaleType($this->cart, $type);
        } catch (ValidationException $e) {
            Toaster::error(collect($e->errors())->flatten()->first() ?? __('app.invalid_sale_type'));

            return;
        } catch (\ValueError) {
            Toaster::error(__('app.invalid_sale_type'));

            return;
        }

        unset($this->cart, $this->breakdown, $this->subtotal, $this->eligiblePlans);
        $this->resetPlanSelection();
        $this->dispatch('shop.cart.updated');

        if ($result['removed'] > 0) {
            Toaster::warning(__('app.items_removed_no_price', ['count' => $result['removed']]));
        } elseif (($result['cash_only'] ?? 0) > 0) {
            Toaster::warning(__('app.cash_items_kept_as_down_payment', ['count' => $result['cash_only']]));
        } else {
            Toaster::success(__('app.sale_type_switched'));
        }
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
        unset($this->cart, $this->breakdown, $this->subtotal, $this->eligiblePlans);
        $this->resetPlanSelection();
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
        unset($this->cart, $this->breakdown, $this->subtotal, $this->eligiblePlans);
        $this->resetPlanSelection();
        $this->dispatch('shop.cart.updated');
        Toaster::success(__('general.deleted'));
    }

    public function validateOrganizationCode(CheckoutService $checkoutService): void
    {
        $key = 'org-code:'.Auth::id().':'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            Toaster::error(__('general.too_many_attempts'));

            return;
        }

        RateLimiter::hit($key, 60);

        $this->validate(
            [
                'organization_code' => ['required', 'string', 'min:8', 'max:32'],
            ],
            [
                'organization_code.required' => __('general.organization_code_required'),
            ],
            [
                'organization_code' => __('general.organization_code'),
            ],
        );

        $organization = $checkoutService->findActiveOrganizationByCode($this->organization_code);

        if ($organization === null) {
            $this->codeValidated = false;
            $this->organizationId = null;
            $this->installment_plan_id = null;
            unset($this->eligiblePlans);
            $this->addError('organization_code', __('general.invalid_organization_code'));

            return;
        }

        RateLimiter::clear($key);
        $this->codeValidated = true;
        $this->organizationId = $organization->id;
        $this->organization_code = $organization->code;
        unset($this->eligiblePlans);
        Toaster::success(__('general.organization_code_valid'));
    }

    public function placeOrder(CheckoutService $checkoutService): void
    {
        if (! $this->codeValidated || $this->organizationId === null) {
            $this->addError('organization_code', __('general.organization_code_required'));

            return;
        }

        if ($this->isInstallmentSale()) {
            $this->validate(
                [
                    'installment_plan_id' => ['required', 'integer'],
                ],
                [],
                [
                    'installment_plan_id' => __('general.select_installment_plan'),
                ],
            );
        }

        try {
            $order = $checkoutService->placeOrder(
                Auth::user(),
                $this->organization_code,
                $this->isInstallmentSale() ? (int) $this->installment_plan_id : null,
            );
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }

            return;
        }

        Toaster::success(__('general.order_submitted'));

        $this->redirect(route('shop.order.success', $order), navigate: true);
    }

    protected function isInstallmentSale(): bool
    {
        return $this->cart?->sale_type === PriceTypeEnum::Installment;
    }

    protected function resetPlanSelection(): void
    {
        $this->installment_plan_id = null;
    }
};
?>

<x-slot name="title">{{ __('general.cart') }} - {{ config('app.name') }}</x-slot>

@php
    $cartService = app(CartService::class);
    $breakdown = $this->breakdown;
@endphp

<div class="mx-auto max-w-5xl space-y-6 px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('general.cart') }}</h1>

    @if ($this->cart === null || $this->cart->items->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-gray-500 dark:border-gray-600">
            {{ __('general.cart_is_empty') }}
        </div>
    @else
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-3 font-semibold text-gray-900 dark:text-white">{{ __('app.select_sale_type') }}</h2>
            <div class="flex flex-wrap gap-3">
                @foreach ([PriceTypeEnum::Cash, PriceTypeEnum::Installment] as $saleType)
                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-4 py-2 text-sm dark:border-gray-700 has-[:checked]:border-brand has-[:checked]:ring-1 has-[:checked]:ring-brand">
                        <input
                            type="radio"
                            name="sale_type"
                            value="{{ $saleType->value }}"
                            @checked($this->cart->sale_type === $saleType)
                            wire:click="setSaleType('{{ $saleType->value }}')"
                        />
                        <span>{{ $saleType->label() }}</span>
                    </label>
                @endforeach
            </div>
            <p class="mt-2 text-sm text-gray-500">{{ __('app.cash_checkout_hint') }}</p>
        </div>

        @if ($this->cart->sale_type === PriceTypeEnum::Installment && bccomp($breakdown['cash_only_subtotal'], '0', 4) > 0)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-200">
                {{ __('app.cash_items_as_down_payment', ['amount' => number_format((float) $breakdown['cash_only_subtotal'])]) }}
            </div>
        @endif

        @if ($this->cart->sale_type === PriceTypeEnum::Installment && bccomp($breakdown['installment_subtotal'], '0', 4) <= 0)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-200">
                {{ __('app.no_installmentable_items_hint') }}
            </div>
        @endif

        <div class="space-y-4">
            @foreach ($this->cart->items as $cartItem)
                @php
                    $isCashOnly = $cartService->lineIsCashOnly($this->cart, $cartItem);
                    $linePriceType = $cartService->linePriceType($this->cart, $cartItem);
                @endphp
                <div class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="font-medium text-gray-900 dark:text-white">{{ $cartItem->item?->title }}</div>
                            @if ($this->cart->sale_type === PriceTypeEnum::Installment)
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-xs font-medium',
                                    'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200' => $isCashOnly,
                                    'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200' => ! $isCashOnly,
                                ])>
                                    {{ $isCashOnly ? __('app.cash_only_badge') : __('app.installment_badge') }}
                                </span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ number_format((float) $cartItem->unit_price) }}
                            <span class="text-xs">({{ $linePriceType->label() }})</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <x-ui.button
                                type="button"
                                size="icon"
                                color="zinc"
                                :loading="false"
                                :disabled="$cartItem->quantity <= 1"
                                wire:click="updateQuantity({{ $cartItem->id }}, {{ $cartItem->quantity - 1 }})"
                                title="{{ __('app.decrease_quantity') }}"
                            >
                                <x-lucide-minus class="h-4 w-4" />
                            </x-ui.button>
                            <span class="min-w-8 text-center text-sm font-medium text-gray-900 dark:text-white">{{ $cartItem->quantity }}</span>
                            <x-ui.button
                                type="button"
                                size="icon"
                                color="teal"
                                :loading="false"
                                wire:click="updateQuantity({{ $cartItem->id }}, {{ $cartItem->quantity + 1 }})"
                                title="{{ __('app.increase_quantity') }}"
                            >
                                <x-lucide-plus class="h-4 w-4" />
                            </x-ui.button>
                        </div>
                        <x-ui.button type="button" color="red" size="sm" :loading="false" wire:click="removeItem({{ $cartItem->id }})">
                            <x-lucide-trash-2 class="h-4 w-4" />
                        </x-ui.button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            @if ($this->cart->sale_type === PriceTypeEnum::Installment)
                <div class="mb-3 space-y-2 text-sm text-gray-600 dark:text-gray-300">
                    <div class="flex justify-between gap-4">
                        <span>{{ __('app.installment_subtotal') }}</span>
                        <div class="text-end">
                            <div>{{ number_format((float) $breakdown['installment_subtotal']) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ PersianNumberToWords::convert($breakdown['installment_subtotal']) }} {{ __('app.rial') }}
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between gap-4">
                        <span>{{ __('app.cash_only_subtotal') }}</span>
                        <div class="text-end">
                            <div>{{ number_format((float) $breakdown['cash_only_subtotal']) }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ PersianNumberToWords::convert($breakdown['cash_only_subtotal']) }} {{ __('app.rial') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mb-4 flex justify-between gap-4 text-lg font-semibold">
                <span>{{ __('general.subtotal') }}</span>
                <div class="text-end">
                    <div>{{ number_format((float) $this->subtotal) }}</div>
                    <div class="text-sm font-normal text-gray-500 dark:text-gray-400">
                        {{ PersianNumberToWords::convert($this->subtotal) }} {{ __('app.rial') }}
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <x-fwb.input
                        wire:model="organization_code"
                        :label="__('general.organization_code')"
                        type="text"
                        dir="ltr"
                        maxlength="32"
                    />
                    @error('organization_code')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <x-ui.button type="button" color="blue" target="validateOrganizationCode" wire:click="validateOrganizationCode" class="w-full sm:w-auto">
                    {{ __('general.validate_organization_code') }}
                </x-ui.button>

                @if ($this->codeValidated)
                    @if ($this->cart->sale_type === PriceTypeEnum::Installment)
                        <div class="space-y-3">
                            <h2 class="font-semibold text-gray-900 dark:text-white">{{ __('general.select_installment_plan') }}</h2>

                            @if (bccomp($breakdown['installment_subtotal'], '0', 4) <= 0)
                                <p class="text-sm text-amber-600">{{ __('app.no_installmentable_items_hint') }}</p>
                            @else
                                @forelse ($this->eligiblePlans as $row)
                                    <label class="flex cursor-pointer gap-3 rounded-lg border border-gray-200 p-3 dark:border-gray-700 has-[:checked]:border-brand has-[:checked]:ring-1 has-[:checked]:ring-brand">
                                        <input type="radio" wire:model="installment_plan_id" value="{{ $row['plan']->id }}" class="mt-1" />
                                        <div class="space-y-1 text-sm">
                                            <div class="font-medium text-gray-900 dark:text-white">{{ $row['plan']->title }}</div>
                                            <div class="text-gray-500">
                                                {{ __('general.term_months') }}: {{ $row['plan']->term_months }}
                                                · {{ __('general.down_payment') }}: {{ number_format((float) $row['preview']['down_payment_amount']) }}
                                                · {{ __('app.plan_down_payment_amount') }}: {{ number_format((float) $row['preview']['plan_down_payment_amount']) }}
                                                · {{ __('general.monthly_payment') }}: {{ number_format((float) $row['preview']['monthly_payment']) }}
                                                · {{ __('general.total_payable') }}: {{ number_format((float) $row['preview']['total_payable']) }}
                                            </div>
                                        </div>
                                    </label>
                                @empty
                                    <p class="text-sm text-amber-600">{{ __('general.no_eligible_installment_plans') }}</p>
                                @endforelse
                            @endif

                            @error('installment_plan_id')
                                <p class="text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($this->eligiblePlans->isNotEmpty())
                            <x-ui.button type="button" color="green" target="placeOrder" wire:click="placeOrder" class="w-full" wire:confirm="{{ __('general.are_you_sure') }}">
                                {{ __('general.submit_order') }}
                            </x-ui.button>
                        @endif
                    @else
                        <p class="text-sm text-gray-500">{{ __('app.cash_checkout_hint') }}</p>
                        <x-ui.button type="button" color="green" target="placeOrder" wire:click="placeOrder" class="w-full" wire:confirm="{{ __('general.are_you_sure') }}">
                            {{ __('general.submit_order') }}
                        </x-ui.button>
                    @endif
                @endif
            </div>
        </div>
    @endif
</div>
