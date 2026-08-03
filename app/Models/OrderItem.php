<?php

namespace App\Models;

use App\Enums\PriceTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'item_id',
    'item_price_id',
    'price_type',
    'title',
    'quantity',
    'unit_price',
    'line_total',
    'meta',
])]
class OrderItem extends Model
{
    protected function casts(): array
    {
        return [
            'price_type' => PriceTypeEnum::class,
            'quantity' => 'integer',
            'unit_price' => 'decimal:4',
            'line_total' => 'decimal:4',
            'meta' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function itemPrice(): BelongsTo
    {
        return $this->belongsTo(ItemPrice::class);
    }
}
