<?php

namespace App\Models;

use App\Services\Shop\BannerCache;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable([
    'title',
    'description',
    'link_url',
    'sort_order',
    'is_active',
    'clicks_count',
])]
class Banner extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'clicks_count' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => BannerCache::forget());
        static::deleted(fn () => BannerCache::forget());
    }

    /**
     * @param  Builder<Banner>  $query
     * @return Builder<Banner>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where($query->getModel()->getTable().'.is_active', true);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banner_image')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->format('webp')
            ->width(400)
            ->height(225)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('optimized')
            ->format('webp')
            ->quality(80)
            ->width(1920)
            ->nonQueued();
    }
}
