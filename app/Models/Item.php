<?php

namespace App\Models;

use App\Enums\ItemTypeEnum;
use App\Enums\PriceTypeEnum;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
    'domain_id',
    'brand_id',
    'category_id',
    'item_type',
    'group_id',
    'is_main',
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
        });
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

    public function groupVariants(): HasMany
    {
        return $this->hasMany(self::class, 'group_id', 'group_id');
    }

    public function latestPriceByType(PriceTypeEnum $type): HasOne
    {
        return $this->hasOne(ItemPrice::class)
            ->where('price_type', $type)
            ->latestOfMany();
    }

    public function getLatestPrice(?PriceTypeEnum $type = null): ?string
    {
        $type ??= config('main.default_price_type');

        return $this->latestPriceByType($type)->first()?->price;
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
