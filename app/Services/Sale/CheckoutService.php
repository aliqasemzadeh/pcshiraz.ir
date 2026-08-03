<?php

namespace App\Services\Sale;

use App\Enums\OrderInstallmentStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PriceTypeEnum;
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
        int $installmentPlanId,
        PriceTypeEnum $saleType = PriceTypeEnum::Installment,
    ): Order {
        $organization = $this->findActiveOrganizationByCode($organizationCode);

        if ($organization === null) {
            throw ValidationException::withMessages([
                'organization_code' => __('general.invalid_organization_code'),
            ]);
        }

        $cart = $this->cartService->getOrCreateCart($user)->load(['items.item']);

        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => __('general.cart_is_empty'),
            ]);
        }

        $subtotal = $this->cartService->subtotal($cart);

        $eligible = $this->matcher->eligiblePlans($organization, $subtotal);
        $selected = $eligible->first(fn (array $row) => $row['plan']->id === $installmentPlanId);

        if ($selected === null) {
            throw ValidationException::withMessages([
                'installment_plan_id' => __('general.installment_plan_not_eligible'),
            ]);
        }

        /** @var InstallmentPlan $plan */
        $plan = $selected['plan'];
        $preview = $selected['preview'];

        return DB::transaction(function () use ($user, $organization, $cart, $plan, $preview, $subtotal, $saleType) {
            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'installment_plan_id' => $plan->id,
                'sale_type' => $saleType,
                'status' => OrderStatusEnum::PendingApproval,
                'subtotal' => $subtotal,
                'discount' => 0,
                'total_amount' => $subtotal,
                'plan_term_months' => $plan->term_months,
                'plan_down_payment_percent' => $preview['effective_down_payment_percent'],
                'plan_monthly_interest_percent' => $plan->monthly_interest_percent,
                'plan_max_financiable_amount' => $plan->max_financiable_amount,
                'down_payment_amount' => $preview['down_payment_amount'],
                'financed_amount' => $preview['financed_amount'],
                'total_interest' => $preview['total_interest'],
                'total_payable' => $preview['total_payable'],
                'outstanding_balance' => $preview['total_payable'],
                'submitted_at' => now(),
            ]);

            foreach ($cart->items as $cartItem) {
                $order->items()->create([
                    'item_id' => $cartItem->item_id,
                    'item_price_id' => $cartItem->item_price_id,
                    'title' => $cartItem->item?->title ?? __('general.item'),
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                    'line_total' => bcmul((string) $cartItem->unit_price, (string) $cartItem->quantity, 4),
                ]);
            }

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

    protected function generateOrderNumber(): string
    {
        do {
            $number = 'ORG-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
