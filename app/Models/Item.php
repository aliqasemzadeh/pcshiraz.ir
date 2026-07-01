<?php

namespace App\Models;

use App\Enums\ItemTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Tags\HasTags;

class Item extends Model implements HasMedia
{
    use InteractsWithMedia, HasTags, SoftDeletes;

    protected function casts(): array
    {
        return [
            'item_type' => ItemTypeEnum::class,
            'is_main' => 'boolean',
        ];
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function itemPrices(): HasMany
    {
        return $this->hasMany(ItemPrice::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product_image')
            ->singleFile();

        $this->addMediaCollection('gallery');
    }
}
