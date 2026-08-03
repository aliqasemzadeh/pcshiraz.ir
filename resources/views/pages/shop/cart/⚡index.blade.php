<?php

use App\Services\Sale\CartService;
use App\Services\Sale\CheckoutService;
use App\Services\Sale\InstallmentPlanMatcher;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
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

        return app(CartService::class)->getOrCreateCart($user)->load(['items.item.media']);
    }

    #[Computed]
    public function subtotal(): string
    {
        if ($this->cart === null) {
            return '0.0000';
        }

        return app(CartService::class)->subtotal($this->cart);
    }

    #[Computed]
    public function eligiblePlans()
    {
        if (! $this->codeValidated || $this->organizationId === null) {
            return collect();
        }

        $organization = \App\Models\Organization::query()->find($this->organizationId);

        if ($organization === null) {
            return collect();
        }

        return app(InstallmentPlanMatcher::class)->eligiblePlans($organization, $this->subtotal);
    }

    public function mount(): void
    {
        if (! Auth::check()) {
            $this->redirect(route('login'), navigate: true);
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
        unset($this->cart, $this->subtotal, $this->eligiblePlans);
        $this->resetPlanSelection();
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
        unset($this->cart, $this->subtotal, $this->eligiblePlans);
        $this->resetPlanSelection();
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

        $this->validate([
            'organization_code' => ['required', 'string', 'min:8', 'max:32'],
        ]);

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

        $this->validate([
            'installment_plan_id' => ['required', 'integer'],
        ]);

        $order = $checkoutService->placeOrder(
            Auth::user(),
            $this->organization_code,
            (int) $this->installment_plan_id,
        );

        Toaster::success(__('general.order_submitted'));

        $this->redirect(route('shop.order.success', $order), navigate: true);
    }

    protected function resetPlanSelection(): void
    {
        $this->installment_plan_id = null;
    }
};
?>

<x-slot name="title">{{ __('general.cart') }} - {{ config('app.name') }}</x-slot>

<div class="mx-auto max-w-5xl space-y-6 px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('general.cart') }}</h1>

    @if ($this->cart === null || $this->cart->items->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center text-gray-500 dark:border-gray-600">
            {{ __('general.cart_is_empty') }}
        </div>
    @else
        <div class="space-y-4">
            @foreach ($this->cart->items as $cartItem)
                <div class="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ $cartItem->item?->title }}</div>
                        <div class="text-sm text-gray-500">{{ number_format((float) $cartItem->unit_price) }}</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <input
                            type="number"
                            min="1"
                            class="w-24 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900"
                            value="{{ $cartItem->quantity }}"
                            wire:change="updateQuantity({{ $cartItem->id }}, $event.target.value)"
                        />
                        <x-ui.button type="button" color="red" size="sm" :loading="false" wire:click="removeItem({{ $cartItem->id }})">
                            <x-lucide-trash-2 class="h-4 w-4" />
                        </x-ui.button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-4 flex justify-between text-lg font-semibold">
                <span>{{ __('general.subtotal') }}</span>
                <span>{{ number_format((float) $this->subtotal) }}</span>
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
                    <div class="space-y-3">
                        <h2 class="font-semibold text-gray-900 dark:text-white">{{ __('general.select_installment_plan') }}</h2>

                        @forelse ($this->eligiblePlans as $row)
                            <label class="flex cursor-pointer gap-3 rounded-lg border border-gray-200 p-3 dark:border-gray-700 has-[:checked]:border-brand has-[:checked]:ring-1 has-[:checked]:ring-brand">
                                <input type="radio" wire:model="installment_plan_id" value="{{ $row['plan']->id }}" class="mt-1" />
                                <div class="space-y-1 text-sm">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $row['plan']->title }}</div>
                                    <div class="text-gray-500">
                                        {{ __('general.term_months') }}: {{ $row['plan']->term_months }}
                                        · {{ __('general.down_payment') }}: {{ number_format((float) $row['preview']['down_payment_amount']) }}
                                        · {{ __('general.monthly_payment') }}: {{ number_format((float) $row['preview']['monthly_payment']) }}
                                        · {{ __('general.total_payable') }}: {{ number_format((float) $row['preview']['total_payable']) }}
                                    </div>
                                </div>
                            </label>
                        @empty
                            <p class="text-sm text-amber-600">{{ __('general.no_eligible_installment_plans') }}</p>
                        @endforelse

                        @error('installment_plan_id')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-ui.button type="button" color="green" target="placeOrder" wire:click="placeOrder" class="w-full" wire:confirm="{{ __('general.are_you_sure') }}">
                        {{ __('general.submit_order') }}
                    </x-ui.button>
                @endif
            </div>
        </div>
    @endif
</div>
