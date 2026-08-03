<?php

namespace App\Models;

use App\Enums\OrderInstallmentStatusEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'sequence',
    'due_date',
    'principal_amount',
    'interest_amount',
    'total_amount',
    'paid_amount',
    'status',
    'paid_at',
    'payment_reference',
])]
class OrderInstallment extends Model
{
    protected function casts(): array
    {
        return [
            'sequence' => 'integer',
            'due_date' => 'date',
            'principal_amount' => 'decimal:4',
            'interest_amount' => 'decimal:4',
            'total_amount' => 'decimal:4',
            'paid_amount' => 'decimal:4',
            'status' => OrderInstallmentStatusEnum::class,
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isDownPayment(): bool
    {
        return $this->sequence === 0;
    }
}
