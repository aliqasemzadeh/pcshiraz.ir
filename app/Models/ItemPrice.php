<?php

namespace App\Models;

use App\Enums\PriceTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPrice extends Model
{
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
