<?php

namespace App\Models;

use App\Enums\PriceTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['item_id', 'price_type', 'price', 'sale_price', 'meta', 'sales_cap', 'total_sales_count', 'is_active'])]
class ItemPrice extends Model
{
    protected static function booted(): void
    {
        static::creating(function (ItemPrice $itemPrice) {
            if ($itemPrice->is_active) {
                static::where('item_id', $itemPrice->item_id)
                    ->where('price_type', $itemPrice->price_type)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }
        });

        static::updating(function (ItemPrice $itemPrice) {
            if ($itemPrice->isDirty('is_active') && $itemPrice->is_active) {
                static::where('item_id', $itemPrice->item_id)
                    ->where('price_type', $itemPrice->price_type)
                    ->where('is_active', true)
                    ->where('id', '!=', $itemPrice->id)
                    ->update(['is_active' => false]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'price_type' => PriceTypeEnum::class,
            'price' => 'decimal:4',
            'sale_price' => 'decimal:4',
            'meta' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
