<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case AwaitingDownPayment = 'awaiting_down_payment';
    case InstallmentActive = 'installment_active';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __('enums.order_status.'.$this->value);
    }

    public static function labels(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $case) => [$case->value => $case->label()])
            ->all();
    }

    public function isEditable(): bool
    {
        return in_array($this, [
            self::PendingApproval,
            self::Approved,
            self::AwaitingDownPayment,
            self::InstallmentActive,
            self::Processing,
        ], true);
    }
}
