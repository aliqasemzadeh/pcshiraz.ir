<?php

namespace App\Models;

use App\Enums\ItemTypeEnum;
use App\Enums\PriceTypeEnum;
use App\Services\Shop\CatalogCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\HasTags;

#[Fillable([
    'brand_id',
    'category_id',
    'item_type',
    'group_id',
    'is_main',
    'is_active',
    'is_purchasable',
    'is_contact_price',
    'views_count',
    'title',
    'slug',
    'description',
    'color_code',
    'color_name',
    'weight',
    'length',
    'width',
    'height',
    'seo_title',
    'meta_description',
    'meta',
])]
class Item extends Model implements HasMedia
{
    use HasTags, InteractsWithMedia, SoftDeletes;

    protected function casts(): array
    {
        return [
            'item_type' => ItemTypeEnum::class,
            'is_main' => 'boolean',
            'is_active' => 'boolean',
            'is_purchasable' => 'boolean',
            'is_contact_price' => 'boolean',
            'views_count' => 'integer',
            'meta' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (Item $item): void {
            if ($item->group_id === null) {
                $item->forceFill(['group_id' => $item->id])->saveQuietly();
            }

            if ($item->is_main && $item->group_id !== null) {
                static::query()
                    ->where('group_id', $item->group_id)
                    ->where('id', '!=', $item->id)
                    ->where('is_main', true)
                    ->update(['is_main' => false]);
            }

            CatalogCache::forgetAll();
            CatalogCache::forgetCategory((int) $item->category_id);
        });

        static::deleted(function (Item $item): void {
            CatalogCache::forgetAll();
            CatalogCache::forgetCategory((int) $item->category_id);
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where($query->getModel()->getTable().'.is_active', true);
    }

    public function scopePurchasable(Builder $query): Builder
    {
        return $query->where($query->getModel()->getTable().'.is_purchasable', true);
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

    public function groupVariants(): HasMany
    {
        return $this->hasMany(self::class, 'group_id', 'group_id');
    }

    public function latestPriceByType(PriceTypeEnum $type): HasOne
    {
        return $this->hasOne(ItemPrice::class)
            ->where('price_type', $type)
            ->where('is_active', true)
            ->latestOfMany();
    }

    public function activeCashPrice(): HasOne
    {
        return $this->latestPriceByType(PriceTypeEnum::Cash);
    }

    public function getLatestPrice(?PriceTypeEnum $type = null): ?string
    {
        $type ??= config('main.default_price_type');

        return $this->latestPriceByType($type)->first()?->sale_price;
    }

    public function hasDiscount(): bool
    {
        $price = $this->activeCashPrice;

        if ($price === null) {
            return false;
        }

        return (float) $price->sale_price < (float) $price->price;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product_image')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
            ->singleFile();

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('optimized')
            ->format('webp')
            ->quality(80)
            ->nonQueued();
    }
}
