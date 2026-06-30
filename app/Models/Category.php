<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Category extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'domain_id',
        'title',
        'seo_title',
        'slug',
        'meta',
        'sort_order',
    ];

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
        $this->addMediaCollection('main_image')
            ->singleFile();
    }
}
