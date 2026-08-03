<?php

namespace App\Enums;

enum OrderInstallmentStatusEnum: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __('enums.order_installment_status.'.$this->value);
    }
}
