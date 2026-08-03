<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Enums\PriceTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'order_number',
    'organization_id',
    'user_id',
    'installment_plan_id',
    'sale_type',
    'status',
    'subtotal',
    'discount',
    'total_amount',
    'cash_only_subtotal',
    'installment_subtotal',
    'plan_term_months',
    'plan_down_payment_percent',
    'plan_monthly_interest_percent',
    'plan_max_financiable_amount',
    'plan_down_payment_amount',
    'down_payment_amount',
    'financed_amount',
    'total_interest',
    'total_payable',
    'outstanding_balance',
    'submitted_at',
    'approved_by_user_id',
    'approved_at',
    'rejected_by_user_id',
    'rejected_at',
    'rejection_note',
    'shipped_at',
    'note',
])]
class Order extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'sale_type' => PriceTypeEnum::class,
            'status' => OrderStatusEnum::class,
            'subtotal' => 'decimal:4',
            'discount' => 'decimal:4',
            'total_amount' => 'decimal:4',
            'cash_only_subtotal' => 'decimal:4',
            'installment_subtotal' => 'decimal:4',
            'plan_term_months' => 'integer',
            'plan_down_payment_percent' => 'decimal:2',
            'plan_monthly_interest_percent' => 'decimal:4',
            'plan_max_financiable_amount' => 'decimal:4',
            'plan_down_payment_amount' => 'decimal:4',
            'down_payment_amount' => 'decimal:4',
            'financed_amount' => 'decimal:4',
            'total_interest' => 'decimal:4',
            'total_payable' => 'decimal:4',
            'outstanding_balance' => 'decimal:4',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'shipped_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function installmentPlan(): BelongsTo
    {
        return $this->belongsTo(InstallmentPlan::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(OrderInstallment::class)->orderBy('sequence');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_user_id');
    }
}
