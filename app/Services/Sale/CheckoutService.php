<?php

namespace App\Services\Sale;

use App\Enums\OrderInstallmentStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PriceTypeEnum;
use App\Models\Cart;
use App\Models\InstallmentPlan;
use App\Models\Order;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    public function __construct(
        protected InstallmentScheduleCalculator $calculator,
        protected InstallmentPlanMatcher $matcher,
        protected CartService $cartService,
    ) {}

    public function findActiveOrganizationByCode(string $code): ?Organization
    {
        $code = Str::upper(trim($code));

        if ($code === '') {
            return null;
        }

        return Organization::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();
    }

    public function placeOrder(
        User $user,
        string $organizationCode,
        ?int $installmentPlanId = null,
    ): Order {
        $organization = $this->findActiveOrganizationByCode($organizationCode);

        if ($organization === null) {
            throw ValidationException::withMessages([
                'organization_code' => __('general.invalid_organization_code'),
            ]);
        }

        $cart = $this->cartService->getOrCreateCart($user)->load(['items.item', 'items.itemPrice']);

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => __('general.cart_is_empty'),
            ]);
        }

        $saleType = $cart->sale_type ?? PriceTypeEnum::Cash;

        if (! in_array($saleType, $this->cartService->allowedSaleTypes(), true)) {
            throw ValidationException::withMessages([
                'sale_type' => __('app.invalid_sale_type'),
            ]);
        }

        $breakdown = $this->cartService->breakdown($cart);

        if ($saleType === PriceTypeEnum::Installment) {
            return $this->placeInstallmentOrder(
                $user,
                $organization,
                $cart,
                $breakdown,
                $installmentPlanId,
            );
        }

        return $this->placeCashOrder($user, $organization, $cart, $breakdown);
    }

    /**
     * @param  array{subtotal: string, cash_only_subtotal: string, installment_subtotal: string, cash_only_count: int}  $breakdown
     */
    protected function placeCashOrder(
        User $user,
        Organization $organization,
        Cart $cart,
        array $breakdown,
    ): Order {
        $subtotal = $breakdown['subtotal'];

        return DB::transaction(function () use ($user, $organization, $cart, $subtotal) {
            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'installment_plan_id' => null,
                'sale_type' => PriceTypeEnum::Cash,
                'status' => OrderStatusEnum::PendingApproval,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total_amount' => $subtotal,
                'cash_only_subtotal' => 0,
                'installment_subtotal' => 0,
                'plan_term_months' => null,
                'plan_down_payment_percent' => null,
                'plan_monthly_interest_percent' => null,
                'plan_max_financiable_amount' => null,
                'plan_down_payment_amount' => 0,
                'down_payment_amount' => 0,
                'financed_amount' => 0,
                'total_interest' => 0,
                'total_payable' => $subtotal,
                'outstanding_balance' => $subtotal,
                'submitted_at' => now(),
            ]);

            $this->createOrderItems($order, $cart);
            $this->cartService->clear($cart);

            return $order->load(['items', 'installments', 'organization']);
        });
    }

    /**
     * @param  array{subtotal: string, cash_only_subtotal: string, installment_subtotal: string, cash_only_count: int}  $breakdown
     */
    protected function placeInstallmentOrder(
        User $user,
        Organization $organization,
        Cart $cart,
        array $breakdown,
        ?int $installmentPlanId,
    ): Order {
        if ($installmentPlanId === null) {
            throw ValidationException::withMessages([
                'installment_plan_id' => __('app.installment_requires_eligible_plan'),
            ]);
        }

        if (bccomp($breakdown['installment_subtotal'], '0', 4) <= 0) {
            throw ValidationException::withMessages([
                'installment_plan_id' => __('app.no_installmentable_items_hint'),
            ]);
        }

        $eligible = $this->matcher->eligiblePlans(
            $organization,
            $breakdown['installment_subtotal'],
            $breakdown['cash_only_subtotal'],
        );
        $selected = $eligible->first(fn (array $row) => $row['plan']->id === $installmentPlanId);

        if ($selected === null) {
            throw ValidationException::withMessages([
                'installment_plan_id' => __('general.installment_plan_not_eligible'),
            ]);
        }

        /** @var InstallmentPlan $plan */
        $plan = $selected['plan'];
        $preview = $selected['preview'];
        $subtotal = $breakdown['subtotal'];

        return DB::transaction(function () use ($user, $organization, $cart, $plan, $preview, $subtotal, $breakdown) {
            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'installment_plan_id' => $plan->id,
                'sale_type' => PriceTypeEnum::Installment,
                'status' => OrderStatusEnum::PendingApproval,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total_amount' => $subtotal,
                'cash_only_subtotal' => $breakdown['cash_only_subtotal'],
                'installment_subtotal' => $breakdown['installment_subtotal'],
                'plan_term_months' => $plan->term_months,
                'plan_down_payment_percent' => $preview['effective_down_payment_percent'],
                'plan_monthly_interest_percent' => $plan->monthly_interest_percent,
                'plan_max_financiable_amount' => $plan->max_financiable_amount,
                'plan_down_payment_amount' => $preview['plan_down_payment_amount'],
                'down_payment_amount' => $preview['down_payment_amount'],
                'financed_amount' => $preview['financed_amount'],
                'total_interest' => $preview['total_interest'],
                'total_payable' => $preview['total_payable'],
                'outstanding_balance' => $preview['total_payable'],
                'submitted_at' => now(),
            ]);

            $this->createOrderItems($order, $cart);

            foreach ($preview['schedule'] as $row) {
                $order->installments()->create([
                    'sequence' => $row['sequence'],
                    'due_date' => $row['due_date'],
                    'principal_amount' => $row['principal_amount'],
                    'interest_amount' => $row['interest_amount'],
                    'total_amount' => $row['total_amount'],
                    'paid_amount' => 0,
                    'status' => OrderInstallmentStatusEnum::Pending,
                ]);
            }

            $this->cartService->clear($cart);

            return $order->load(['items', 'installments', 'organization']);
        });
    }

    protected function createOrderItems(Order $order, Cart $cart): void
    {
        foreach ($cart->items as $cartItem) {
            $priceType = $this->cartService->linePriceType($cart, $cartItem);

            $order->items()->create([
                'item_id' => $cartItem->item_id,
                'item_price_id' => $cartItem->item_price_id,
                'price_type' => $priceType,
                'title' => $cartItem->item?->title ?? __('general.item'),
                'quantity' => $cartItem->quantity,
                'unit_price' => $cartItem->unit_price,
                'line_total' => bcmul((string) $cartItem->unit_price, (string) $cartItem->quantity, 4),
            ]);
        }
    }

    protected function generateOrderNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
