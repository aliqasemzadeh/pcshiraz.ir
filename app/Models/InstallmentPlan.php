<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'title',
    'organization_id',
    'term_months',
    'down_payment_percent',
    'monthly_interest_percent',
    'max_financiable_amount',
    'down_payment_required_above',
    'min_down_payment_percent',
    'min_order_amount',
    'priority',
    'is_active',
    'starts_at',
    'ends_at',
])]
class InstallmentPlan extends Model
{
    protected function casts(): array
    {
        return [
            'term_months' => 'integer',
            'down_payment_percent' => 'decimal:2',
            'monthly_interest_percent' => 'decimal:4',
            'max_financiable_amount' => 'decimal:4',
            'down_payment_required_above' => 'decimal:4',
            'min_down_payment_percent' => 'decimal:2',
            'min_order_amount' => 'decimal:4',
            'priority' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function organizations(): BelongsToMany
    {
        return $this->belongsToMany(Organization::class, 'organization_installment_plan')
            ->withPivot(['is_default', 'is_active', 'priority'])
            ->withTimestamps();
    }

    public function isCurrentlyValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at !== null && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at !== null && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isGlobal(): bool
    {
        return $this->organization_id === null;
    }
}
