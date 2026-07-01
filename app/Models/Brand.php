<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable([
    'domain_id',
    'title',
    'slug',
    'seo_title',
    'sort_order',
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
        ];
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo_image')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif'])
            ->singleFile();
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
