<?php

namespace App\Services\Sale;

use App\Enums\OrderInstallmentStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PriceTypeEnum;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderApprovalService
{
    public function __construct(
        protected InstallmentScheduleCalculator $calculator,
    ) {}

    public function approve(Order $order, User $approver): Order
    {
        $this->assertPending($order);
        $this->assertApprover($order, $approver);

        return DB::transaction(function () use ($order, $approver) {
            $order->refresh();

            if ($order->status !== OrderStatusEnum::PendingApproval) {
                throw ValidationException::withMessages([
                    'order' => __('general.order_already_processed'),
                ]);
            }

            $nextStatus = bccomp((string) $order->down_payment_amount, '0', 4) > 0
                ? OrderStatusEnum::AwaitingDownPayment
                : OrderStatusEnum::InstallmentActive;

            $order->update([
                'status' => $nextStatus,
                'approved_by_user_id' => $approver->id,
                'approved_at' => now(),
            ]);

            return $order->refresh();
        });
    }

    public function reject(Order $order, User $approver, ?string $note = null): Order
    {
        $this->assertPending($order);
        $this->assertApprover($order, $approver);

        return DB::transaction(function () use ($order, $approver, $note) {
            $order->refresh();

            if ($order->status !== OrderStatusEnum::PendingApproval) {
                throw ValidationException::withMessages([
                    'order' => __('general.order_already_processed'),
                ]);
            }

            $order->update([
                'status' => OrderStatusEnum::Rejected,
                'rejected_by_user_id' => $approver->id,
                'rejected_at' => now(),
                'rejection_note' => $note,
            ]);

            $order->installments()->update([
                'status' => OrderInstallmentStatusEnum::Cancelled,
            ]);

            return $order->refresh();
        });
    }

    public function markShipped(Order $order): Order
    {
        if (! in_array($order->status, [
            OrderStatusEnum::Approved,
            OrderStatusEnum::AwaitingDownPayment,
            OrderStatusEnum::InstallmentActive,
            OrderStatusEnum::Processing,
        ], true)) {
            throw ValidationException::withMessages([
                'order' => __('general.order_cannot_ship'),
            ]);
        }

        $order->update([
            'status' => OrderStatusEnum::Shipped,
            'shipped_at' => now(),
        ]);

        return $order->refresh();
    }

    /**
     * @param  list<array{id: int, quantity: int}>  $items
     */
    public function updateItems(Order $order, array $items): Order
    {
        if (! $order->status->isEditable() || $order->status === OrderStatusEnum::Shipped) {
            throw ValidationException::withMessages([
                'order' => __('general.order_not_editable'),
            ]);
        }

        return DB::transaction(function () use ($order, $items) {
            $order->load(['items', 'installmentPlan']);

            foreach ($items as $row) {
                $orderItem = $order->items->firstWhere('id', $row['id']);

                if ($orderItem === null) {
                    continue;
                }

                $quantity = max(0, (int) $row['quantity']);

                if ($quantity === 0) {
                    $orderItem->delete();

                    continue;
                }

                $orderItem->update([
                    'quantity' => $quantity,
                    'line_total' => bcmul((string) $orderItem->unit_price, (string) $quantity, 4),
                ]);
            }

            $order->load('items');

            $subtotal = '0.0000';
            $cashOnlySubtotal = '0.0000';
            $installmentSubtotal = '0.0000';

            foreach ($order->items as $item) {
                $lineTotal = (string) $item->line_total;
                $subtotal = bcadd($subtotal, $lineTotal, 4);

                if ($item->price_type === PriceTypeEnum::Installment) {
                    $installmentSubtotal = bcadd($installmentSubtotal, $lineTotal, 4);
                } elseif ($order->sale_type === PriceTypeEnum::Installment) {
                    $cashOnlySubtotal = bcadd($cashOnlySubtotal, $lineTotal, 4);
                }
            }

            if (bccomp($subtotal, '0', 4) <= 0) {
                throw ValidationException::withMessages([
                    'order' => __('general.order_must_have_items'),
                ]);
            }

            $plan = $order->installmentPlan;

            if ($plan === null) {
                $order->update([
                    'subtotal' => $subtotal,
                    'total_amount' => $subtotal,
                    'cash_only_subtotal' => 0,
                    'installment_subtotal' => 0,
                    'plan_down_payment_amount' => 0,
                    'total_payable' => $subtotal,
                    'outstanding_balance' => $subtotal,
                ]);

                return $order->refresh();
            }

            $preview = $this->calculator->calculate($plan, $installmentSubtotal, $cashOnlySubtotal);

            $order->installments()->delete();

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

            $order->update([
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
                'cash_only_subtotal' => $cashOnlySubtotal,
                'installment_subtotal' => $installmentSubtotal,
                'plan_down_payment_percent' => $preview['effective_down_payment_percent'],
                'plan_down_payment_amount' => $preview['plan_down_payment_amount'],
                'down_payment_amount' => $preview['down_payment_amount'],
                'financed_amount' => $preview['financed_amount'],
                'total_interest' => $preview['total_interest'],
                'total_payable' => $preview['total_payable'],
                'outstanding_balance' => $preview['total_payable'],
            ]);

            return $order->refresh()->load(['items', 'installments']);
        });
    }

    protected function assertPending(Order $order): void
    {
        if ($order->status !== OrderStatusEnum::PendingApproval) {
            throw ValidationException::withMessages([
                'order' => __('general.order_already_processed'),
            ]);
        }
    }

    protected function assertApprover(Order $order, User $approver): void
    {
        $order->loadMissing('organization');

        if (! $order->organization->isApprover($approver)) {
            throw ValidationException::withMessages([
                'order' => __('general.not_organization_approver'),
            ]);
        }
    }
}
