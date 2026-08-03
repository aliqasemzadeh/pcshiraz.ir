<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'cart_id',
    'item_id',
    'item_price_id',
    'quantity',
    'unit_price',
])]
class CartItem extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:4',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function itemPrice(): BelongsTo
    {
        return $this->belongsTo(ItemPrice::class);
    }

    public function getLineTotalAttribute(): string
    {
        return bcmul((string) $this->unit_price, (string) $this->quantity, 4);
    }
}
