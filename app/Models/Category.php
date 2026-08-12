<?php

namespace App\Models;

use App\Services\Shop\CatalogCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
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
    'show_on_home',
    'views_count',
    'meta',
])]
class Category extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'sort_order' => 'integer',
            'show_on_home' => 'boolean',
            'views_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => CatalogCache::forgetAll());
        static::deleted(fn () => CatalogCache::forgetAll());
    }

    public function scopeShowOnHome(Builder $query): Builder
    {
        return $query->where('show_on_home', true);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class, 'items', 'category_id', 'brand_id')
            ->whereNull('items.deleted_at')
            ->distinct();
    }

    /**
     * @return array{category: self, slug: string}
     */
    public function shopRoute(): array
    {
        return [
            'category' => $this,
            'slug' => $this->slug,
        ];
    }

    /**
     * @return array{category: self, slug: string, brand: Brand, brandSlug: string}
     */
    public function shopBrandRoute(Brand $brand): array
    {
        return [
            'category' => $this,
            'slug' => $this->slug,
            'brand' => $brand,
            'brandSlug' => $brand->slug,
        ];
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
