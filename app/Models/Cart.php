<?php

namespace App\Models;

use App\Enums\PriceTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id',
    'sale_type',
])]
class Cart extends Model
{
    protected function casts(): array
    {
        return [
            'sale_type' => PriceTypeEnum::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function isInstallment(): bool
    {
        return $this->sale_type === PriceTypeEnum::Installment;
    }

    public function isCash(): bool
    {
        return $this->sale_type === PriceTypeEnum::Cash;
    }

    public function getSubtotalAttribute(): string
    {
        return (string) $this->items->sum(fn (CartItem $item) => (float) $item->unit_price * $item->quantity);
    }

    public function getItemsCountAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }
}
