<?php

namespace App\Models;

use App\Services\Shop\CatalogCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable([
    'title',
    'slug',
    'seo_title',
    'sort_order',
    'views_count',
    'meta',
])]
class Brand extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'sort_order' => 'integer',
            'views_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => CatalogCache::forgetAll());
        static::deleted(fn () => CatalogCache::forgetAll());
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'items', 'brand_id', 'category_id')
            ->whereNull('items.deleted_at')
            ->distinct();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo_image')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/svg+xml'])
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if ($media?->mime_type === 'image/svg+xml') {
            return;
        }

        $this->addMediaConversion('thumb')
            ->format('webp')
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
